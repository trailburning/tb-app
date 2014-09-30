define([
  'underscore', 
  'backbone',
  'models/TrailMediaModel',    
  'views/ActivityFeedView',
  'views/TrailMiniMapView',
  'views/TrailSlidesView',  
  'views/TrailMapView',  
  'views/TrailStatsView',  
  'views/TrailAltitudeView',
  'views/TrailWeatherView',
  'views/TrailActivitiesView'
], function(_, Backbone, TrailMediaModel, ActivityFeedView, TrailMiniMapView, TrailSlidesView, TrailMapView, TrailStatsView, TrailAltitudeView, TrailWeatherView, TrailActivitiesView){
  
  var PLAYER_INTRO = 0;
  var PLAYER_PREPARE_SHOW = 1;  
  var PLAYER_SHOW = 2;  
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;

  var HOLD_SLIDE = 5000;
  var TICKLE_TIMER = 5000;

  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;

  var AppView = Backbone.View.extend({
    initialize: function(){
      var self = this;

	  var MediaCollection = Backbone.Collection.extend({
    	comparator: function(item) {
    		// sort by datetime
        	return item.get('tags').datetime;
    	}
	  });
      this.mediaCollection = new MediaCollection();    
      this.mediaModel = new TrailMediaModel();
            
      app.dispatcher.on("TrailMapView:zoominclick", self.onTrailMapViewZoomInClick, this);
      app.dispatcher.on("TrailMapView:zoomoutclick", self.onTrailMapViewZoomOutClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:mediaclick", self.onTrailMapMediaMarkerClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:photoclick", self.onTrailMapMediaPhotoClick, this);      
      app.dispatcher.on("TrailMediaMarkerView:mediaclick", self.onTrailMediaMarkerClick, this);
      app.dispatcher.on("TrailSlidesView:slideview", self.onTrailSlidesViewSlideView, this);
      app.dispatcher.on("TrailSlidesView:clickslideprev", self.onTrailSlidesViewSlideClickPrev, this);
      app.dispatcher.on("TrailSlidesView:clickslidenext", self.onTrailSlidesViewSlideClickNext, this);
            
      this.nPlayerView = PLAYER_INTRO;
      this.nTrailView = SLIDE_VIEW;
      this.nSlideShowState = SLIDESHOW_INIT;
            
      this.nPlayerHeight = 0;
      this.nPlayerMinHeight = $('#trailplayer').height();
      this.nAvatarWidth = 0;
      if ($('#trail_intro_view .trail_avatar').length) {
      	this.nAvatarWidth = $('#trail_intro_view .trail_avatar').width();
      }

      this.slideTimer = null;
      this.nCurrSlide = -1;
      this.nTickleCount = 0;
      this.nOldTickleCount = 0;
      this.userProfileMap = null;
      this.bFirstSlide = true;
      this.bPlayerReady = false;
  
      this.bSlideFull = true;
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
      this.trailStatsView = new TrailStatsView({ el: '#trail_stats_view', model: this.model });
      this.trailAltitudeView = new TrailAltitudeView({ el: '#trail_altitude_view', model: this.model });
      this.trailMiniMapView = new TrailMiniMapView({ el: '#trail_minimap_view', model: this.model });
  
      this.trailSlidesView = new TrailSlidesView({ el: '#trail_slides_view', model: this.mediaModel });
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
      
      this.trailWeatherView = new TrailWeatherView({ el: '#trail_weather_view', model: this.model });
      this.trailActivitiesView = new TrailActivitiesView({ el: '#trailactivities_view', model: this.model, bReadonly: true });
      
      this.buildBtns();      
      this.updatePlayerHeight();
      
      $(window).resize(function() {
        self.handleResize();
      });    
  
      $('#trailplayer').show();
      $('.panel_container').show();
      $('#footerview').show();
      
      // show loader
      $('#tb-loader-overlay').fadeIn();
      
      // get trail    
      this.model.set('id', this.options.nTrail);             
      this.model.fetch({
        success: function () {        
          self.handleTrail();
          
          self.trailMiniMapView.render();
          self.trailMapView.render();
  
          self.mediaModel.url = TB_RESTAPI_BASEURL + '/v1/route/'+self.model.get('id')+'/medias';
          self.mediaModel.fetch({
            success: function () {
              self.handleMedia(self.mediaModel);
            }
          });        
        }      
      });      
    },   
    buildBtns: function(){
      var self = this;

      $('#trail_intro_view .trail_play').click(function(evt){
      	self.showPlayer();
	  });

      $('#trail_intro_view .trail_play').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });

      $('#headerview .close_link').click(function(evt){
      	self.hidePlayer();
	  });

      $('#view_toggle .button').click(function(evt){
        self.onTrailToggleViewBtnClick(evt);
      });
      $('#view_toggle .button').mouseover(function(evt){
        self.onTrailToggleViewBtnOver(evt);
      });
      $('#view_toggle .button').mouseout(function(evt){
        self.onTrailToggleViewBtnOut(evt);
      });

      $('#slideshow_toggle .button').click(function(evt){
        self.toggleSlideshow();
      });
      $('#slideshow_toggle .button').mouseover(function(evt){
        self.onTrailToggleSlideshowBtnOver(evt);      
      });
      $('#slideshow_toggle .button').mouseout(function(evt){
        self.onTrailToggleSlideshowBtnOut(evt);
      });

	  function updateLikeBtn() {
	    if (self.elLikeBtn.hasClass('pressed-btn-tb')) {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-on'));
	    }
	    else {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-off'));
	    }
	  }

  	  $('.like_btn', $(this.el)).click(function(evt){
  	    if ($(this).hasClass('pressed-btn-tb')) {
      	  $(this).removeClass('pressed-btn-tb');
      	  self.like($(this).attr('data-trailid'), false);
  	      updateLikeBtn();
  	    }
        else {
      	  $(this).addClass('pressed-btn-tb');
      	  self.like($(this).attr('data-trailid'), true);
          updateLikeBtn();
  	    }      	
  	  });
    },
    like: function(nTrail, bFollow){    
      var strMethod = 'like';
      if (!bFollow) {
      	strMethod = 'undolike';
      }
    	
      var strURL = TB_RESTAPI_BASEURL + '/v1/route/'+nTrail+'/' + strMethod;
      console.log(strURL);      
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        headers: {'Trailburning-User-ID': TB_USER_ID},
        error: function(data) {
          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
          console.log('success');
          console.log(data);
        }
      });        
    },        
    updatePlayerHeight: function(){
      var nPlayerHeight = 0;      
      var nPlayerViewerHeight = 0;

      var elContentView = $('#content_view');
      var nContentY = elContentView.position().top;
      
      nPlayerHeight = Math.round(elContentView.width() * 0.746875);
      
	  switch (this.nPlayerView) {
	  	case PLAYER_INTRO:
	  	  nPlayerViewerHeight = this.nPlayerMinHeight;
	  	  this.nPlayerHeight = nPlayerHeight;
	  	  
	  	  if (nPlayerHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nPlayerHeight - nPlayerViewerHeight)/2;
            $('#trail_views').css('top', -nAdjustY);        	  	  	
	  	  } 
	  	  break;
	  	
	  	case PLAYER_PREPARE_SHOW:
	  	case PLAYER_SHOW:
	  	  nPlayerViewerHeight = $(window).height() - nContentY;
	  	  
	  	  if (nPlayerHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nPlayerHeight - nPlayerViewerHeight)/2;
            $('#trail_views').css('top', -nAdjustY);        	  	  	
		  }
		  
	  	  if (nPlayerHeight < nPlayerViewerHeight) {
	  	  	// player is smaller than viewer
	  	    nPlayerViewerHeight = nPlayerHeight;
	  	    $('#trail_views').css('top', 0);	  	  	
		  }
		  
      	  this.nPlayerHeight = nPlayerHeight;
	  	  break;
	  }

      $('#trailplayer').height(nPlayerViewerHeight);
      
  	  $('#trail_slides_view').height(this.nPlayerHeight);
   	  // force height update for imageScale
   	  $('#trail_slides_view .image_container').height(this.nPlayerHeight);      	  
	  
   	  $('#trail_map_view').height(this.nPlayerHeight);
   	  // force height update for MapBox
   	  $('#trail_map_view .map_container').height(this.nPlayerHeight);      	  	  
    },
    handleResize: function(){
      // remove transition to avoid seeing grey beneath image when resizing
      $('#trailplayer').removeClass('tb-size');
      $('#trail_views').removeClass('tb-move-vert');
      
      this.updatePlayerHeight();
      
      switch (this.nTrailView) {
        case SLIDE_VIEW:
          break;
           
        case MAP_VIEW:
          $('#trailplayer .map_container').width($('#appview').width());
          this.trailMapView.render();
          break;
      }      
      
      if (this.bPlayerReady) {
        this.trailSlidesView.render();        
        this.trailStatsView.render();
        this.trailAltitudeView.render();
      }
    },
    handleTrail: function(){
      $('#trail_overlay').addClass('tb-move-vert');
      $('#trail_info').addClass('tb-move-vert');
      $('#trail_info .trail_avatar').addClass('tb-move-vert');       
      $('#trail_info .trail_title').addClass('tb-move-vert'); 
      $('#view_player_btns').addClass('tb-move-vert');
      $('#view_map_btns').addClass('tb-move-vert');
      $('#trail_stats_view').addClass('tb-move-vert');
      $('#trail_altitude_view').addClass('tb-move-vert');            
      $('#trail_mini_view').addClass('tb-move-vert');      
      
      // render activities
      this.trailActivitiesView.render();
      if (this.model.get('value').route.attributes != undefined) {
        $('.activity_panel').show();
      }
      
      var self = this;          
      this.nTickleTimer = setInterval(function() {
        self.onTickleTimer();
      }, TICKLE_TIMER);     
      
      $(window).mousemove(function(evt) {
        self.tickle();
      });
      
      var jsonPoint = this.model.get('value').route.route_points[0]; 
      var map = L.mapbox.map('trail_location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      var startIcon = new LocationIcon({iconUrl: 'http://assets.trailburning.com/images/icons/location.png'});
      L.marker([jsonPoint.coords[1], jsonPoint.coords[0]], {icon: startIcon}).addTo(map);      

      var latlng = new L.LatLng(jsonPoint.coords[1], jsonPoint.coords[0]);
      map.setView(latlng, 12);
      
    },
    handleMedia: function(){
      var self = this;
      
      // set hero slide if we have one
      if (this.model.get('value').route.media) {
      	this.trailSlidesView.setHeroSlideId(this.model.get('value').route.media.id);
      }      
      
      var jsonMedia = this.mediaModel.get('value');
      // add to collection
      $.each(jsonMedia, function(key, media) {
        self.mediaCollection.add(new Backbone.Model(media));      
      });
      // iterate collection
 	  this.mediaCollection.each(function(model) {
        self.trailMiniMapView.addMedia(model);
        self.trailMapView.addMedia(model);
        
        self.trailSlidesView.addMedia(model);
        
        self.trailAltitudeView.addMedia(model);
        self.trailWeatherView.render();
      });      
      
      this.trailAltitudeView.renderMarkers();
      this.trailMiniMapView.renderMarkers();          
      this.trailMapView.renderMarkers();
          
      this.bPlayerReady = true;
                      
      this.handleResize();
      
	  // start with hero slide
	  this.nCurrSlide = this.trailSlidesView.getHeroSlide();
      this.trailSlidesView.gotoSlide(this.nCurrSlide);
      
      // keyboard control
      $(document).keydown(function(e){
      	switch (e.keyCode) {
      	  case 13: // toggle overlay
            e.preventDefault();
            self.togglePlayer();
      	    break;
      	  case 32: // toggle slideshow
          	e.preventDefault();
          	self.toggleSlideshow();      	  
      	    break;
      	  case 37: // previos slide
          	self.stopSlideShow();
          	self.prevSlide();         
      	    break;
      	  case 39: // next slide
          	self.stopSlideShow();
          	self.nextSlide();         
      	    break;
      	  case 86: // toggle view
          	self.onTrailToggleViewBtnClick();
      	    break;
      	}
      });
    },
    tickle: function(){
      this.nTickleCount++;

//      this.showDetailOverlay();
    },
    togglePlayer: function(){
      switch (this.nPlayerView) {
	    case PLAYER_INTRO:
	      this.showPlayer();
      	  break;
      		
	 	case PLAYER_SHOW:
	  	  this.hidePlayer();
      	  break;
      }
    },
    showPlayer: function(){
      this.nPlayerView = PLAYER_PREPARE_SHOW;

	  var self = this;

	  $('#headerview .close_link').show();

      // add transition for effect      
      $('#trailplayer').addClass('tb-size');
	  $('#trail_views').addClass('tb-move-vert');
      
      this.updatePlayerHeight();
      
      this.hideIntroOverlay();

      this.nPlayerView = PLAYER_SHOW;
      
      this.updatePlayerHeight();
      
      this.trailMiniMapView.gotoMedia(this.nCurrSlide);
      this.trailMapView.gotoMedia(this.nCurrSlide);
      
      this.trailAltitudeView.gotoMedia(this.nCurrSlide);
      
      setTimeout(function() {
	    self.showOverlay();
      }, 500);
            
      $('#view_player_btns').css('top', 52);
      $('#view_map_btns').css('top', 64);
      
      this.slideTimer = setTimeout(function() {
        self.startSlideShow();
      }, HOLD_SLIDE);      
    },
    hidePlayer: function(){
      this.nPlayerView = PLAYER_INTRO;

	  $('#headerview .close_link').hide();

      // add transition for effect      
      $('#trailplayer').addClass('tb-size');
	  $('#trail_views').addClass('tb-move-vert');
      
      this.updatePlayerHeight();
      
      this.showIntroOverlay();
      
      this.showPhotoView();
      
      $('#view_player_btns').css('top', -100);
      $('#view_map_btns').css('top', -300);
      
      this.stopSlideShow();

      this.trailSlidesView.gotoHeroSlide();
      this.nCurrSlide = this.trailSlidesView.getHeroSlide();
      
      this.hideOverlay();
    },
    showIntroOverlay: function(){    
      $('#trail_intro_view .info-hero').css('left', 0);
      $('#trail_intro_view .info-hero .trail_title').css('left', this.nAvatarWidth + 50);                                	          
    },
    hideIntroOverlay: function(){    
      $('#trail_intro_view .info-hero').css('left', -800);
      $('#trail_intro_view .info-hero .trail_title').css('left', -100);
    },
    showOverlay: function(){
      $('.overlay_background').css('opacity', 1);

      $('#trail_stats_view').css('top', 70);
      $('#trail_altitude_view').css('top', 70);        
      $('#trail_mini_view').css('top', 70);
	},
    hideOverlay: function(){
	  $('.overlay_background').css('opacity', 0);

      $('#trail_stats_view').css('top', 300);
      $('#trail_altitude_view').css('top', 350);
      $('#trail_mini_view').css('top', 400);    
	},
    startSlideShow: function(){
      this.nSlideShowState = SLIDESHOW_PLAYING;

      $('#slideshow_toggle .button').addClass('slideshow_pause');
      $('#slideshow_toggle .button').removeClass('slideshow_play');
          
      this.nextSlide();
    },
    stopSlideShow: function(){
      this.nSlideShowState = SLIDESHOW_STOPPED;
      
      $('#slideshow_toggle .button').addClass('slideshow_play');
      $('#slideshow_toggle .button').removeClass('slideshow_pause');
      
      if (this.slideTimer) {
        clearTimeout(this.slideTimer);
      }
    },    
    nextSlide: function(){
      var nSlide = this.nCurrSlide; 
      if (nSlide < this.mediaCollection.length-1) {
        nSlide++;                               
      }
      else {
        nSlide = 0;
      }
      this.gotoMedia(nSlide);
    },    
    prevSlide: function(){
      var nSlide = this.nCurrSlide; 
      if (nSlide > 0) {
        nSlide--;                               
      }
      else {
        nSlide = this.mediaCollection.length-1;
      }
      this.gotoMedia(nSlide);
    },    
    gotoMedia: function(nSlide){          
      this.trailSlidesView.gotoSlide(nSlide);
      
      this.trailMiniMapView.gotoMedia(nSlide);
      this.trailMapView.gotoMedia(nSlide);
      
      this.trailAltitudeView.gotoMedia(nSlide);
      
      this.nCurrSlide = nSlide;    
      
      // render next slide to avoid stalling when in slide show
      if (nSlide < this.mediaCollection.length-1) {            
        this.trailSlidesView.render(nSlide+1);
      }              
      this.updatePlayerHeight();      
    },
    toggleSlideshow: function(){
      $('#slideshow_toggle .button').removeClass('slideshow_pause_hover');        
      $('#slideshow_toggle .button').removeClass('slideshow_play_hover');        
      
      switch (this.nSlideShowState) {
        case SLIDESHOW_PLAYING:
          this.stopSlideShow();
          if (!Modernizr.touch) {
            $('#slideshow_toggle .button').addClass('slideshow_play_hover');        
          }
          break;
          
        case SLIDESHOW_STOPPED:
          this.startSlideShow();
          if (!Modernizr.touch) {
            $('#slideshow_toggle .button').addClass('slideshow_pause_hover');        
          }
          break;
      }
    },    
    showMapView: function(evt){
      if (this.nTrailView == MAP_VIEW) {
      	return;
      }
    	
      this.nTrailView = MAP_VIEW;
      
      $('#view_toggle .button').addClass('view_photo');
      $('#view_toggle .button').removeClass('view_map');
      if (evt) {
        if (!Modernizr.touch && $(evt.currentTarget).attr('id') == 'view_toggle_btn') {
          $('#view_toggle .button').addClass('view_photo_hover');
        }        
      }
      
      this.trailSlidesView.hide();
      this.trailMapView.show();
      this.trailMapView.render();
      this.trailMapView.enablePopups(true);          
    },
    showPhotoView: function(evt){
      if (this.nTrailView == SLIDE_VIEW) {
      	return;
      }
    	
      this.nTrailView = SLIDE_VIEW;
      
      $('#view_toggle .button').addClass('view_map');
      $('#view_toggle .button').removeClass('view_photo');
      if (evt) {
        if (!Modernizr.touch && $(evt.currentTarget).attr('id') == 'view_toggle_btn') {
          $('#view_toggle .button').addClass('view_map_hover');        
        }
      }
                          
      this.trailMapView.hide();
      this.trailMapView.enablePopups(false);
      this.trailSlidesView.show();
      this.trailSlidesView.render();
    },
    onTickleTimer: function(){
      return;
    	
//      console.log("onTickleTimer:"+this.nOldTickleCount+' : '+this.nTickleCount);
      if (this.nOldTickleCount == this.nTickleCount) {
        this.hideDetailOverlay();
      }
      this.nOldTickleCount = this.nTickleCount;         
    },
    onTrailMapViewZoomInClick: function(mapView){
      this.stopSlideShow();
    },
    onTrailMapViewZoomOutClick: function(mapView){
      this.stopSlideShow();
    },
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      // look up model in collcetion
      var nMedia = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      this.stopSlideShow();
      this.gotoMedia(nMedia);
    },
    onTrailMapMediaPhotoClick: function(mapMediaMarkerView){
      this.onTrailToggleViewBtnClick();
    },
    onTrailMediaMarkerClick: function(mediaMarkerView){
      // look up model in collcetion
      var nMedia = this.mediaCollection.indexOf(mediaMarkerView.model);
      
      this.stopSlideShow();
      this.gotoMedia(nMedia);
    },
    onTrailToggleViewBtnOver: function(evt){
      $(evt.currentTarget).css('cursor','pointer');      
      
      if (Modernizr.touch) {
        return;
      }
      
      switch (this.nTrailView) {
        case MAP_VIEW:
          $('#view_toggle .button').addClass('view_photo_hover');        
          break;
          
        case SLIDE_VIEW:
          $('#view_toggle .button').addClass('view_map_hover');        
          break;
      }
    },
    onTrailToggleViewBtnOut: function(evt){
      if (Modernizr.touch) {
        return;
      }
      
      switch (this.nTrailView) {
        case MAP_VIEW:
          $('#view_toggle .button').removeClass('view_photo_hover');        
          break;
          
        case SLIDE_VIEW:
          $('#view_toggle .button').removeClass('view_map_hover');        
          break;
      }
    },
    onTrailToggleViewBtnClick: function(evt){
      $('#view_toggle .button').removeClass('view_photo_hover');        
      $('#view_toggle .button').removeClass('view_map_hover');        
      
      switch (this.nTrailView) {
        case MAP_VIEW:
          this.showPhotoView(evt);
          break;
          
        case SLIDE_VIEW:
          this.showMapView(evt);
          break;
      }
      this.handleResize();      
    },
    onTrailToggleSlideshowBtnOver: function(evt){
      $(evt.currentTarget).css('cursor','pointer');      
      
      if (Modernizr.touch) {
        return;
      }
      
      switch (this.nSlideShowState) {
        case SLIDESHOW_PLAYING:
          $('#slideshow_toggle .button').addClass('slideshow_pause_hover');        
          break;
          
        case SLIDESHOW_STOPPED:
          $('#slideshow_toggle .button').addClass('slideshow_play_hover');        
          break;
      }
    },
    onTrailToggleSlideshowBtnOut: function(evt){
      if (Modernizr.touch) {
        return;
      }
      
      switch (this.nSlideShowState) {
        case SLIDESHOW_PLAYING:
          $('#slideshow_toggle .button').removeClass('slideshow_pause_hover');        
          break;
          
        case SLIDESHOW_STOPPED:
          $('#slideshow_toggle .button').removeClass('slideshow_play_hover');        
          break;
      }
    },
    onTrailSlidesViewSlideView: function(){
      var self = this;
      
      // start timer
      if (this.slideTimer) {
        clearTimeout(this.slideTimer);
      }
      
      if (this.nSlideShowState == SLIDESHOW_PLAYING) {
        this.slideTimer = setTimeout(function() {
          self.onShowNextSlide();
        }, HOLD_SLIDE);
      }
      
      if (this.bFirstSlide) {
        this.bFirstSlide = false;
        
        self.showIntroOverlay();
      }
    },    
    onTrailSlidesViewSlideClickPrev: function(){
      this.stopSlideShow();
      this.prevSlide();         
    },
    onTrailSlidesViewSlideClickNext: function(){
      this.stopSlideShow();
      this.nextSlide();         
    },
    onShowNextSlide: function(){
      this.nextSlide();          
    }
    
  });

  return AppView;
});
