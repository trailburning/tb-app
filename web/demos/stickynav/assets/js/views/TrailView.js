define([
  'underscore', 
  'backbone',
  'models/TrailMediaModel',    
  'views/ActivityFeedView',
  'views/TrailMiniMapView',
  'views/TrailMiniSlidesView',
  'views/TrailSlidesView',  
  'views/TrailMapView',  
  'views/TrailStatsView',  
  'views/TrailAltitudeView',
  'views/TrailWeatherView',
  'views/TrailActivitiesView'
], function(_, Backbone, TrailMediaModel, ActivityFeedView, TrailMiniMapView, TrailMiniSlidesView, TrailSlidesView, TrailMapView, TrailStatsView, TrailAltitudeView, TrailWeatherView, TrailActivitiesView){

  var MIN_HEIGHT = 540;
  var PLAYER_REDUCE_HEIGHT = 50;
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;

  var HOLD_SLIDE = 5000;
  var TICKLE_TIMER = 5000;

  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;

  var TITLE_OFF = 0;
  var TITLE_ON = 1;

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
      
      this.nTrailView = SLIDE_VIEW;
      this.nTitleState = TITLE_OFF;
      this.nSlideShowState = SLIDESHOW_INIT;
      this.nPlayerHeight = 0;
      this.slideTimer = null;
      this.nCurrSlide = -1;
      this.nTickleCount = 0;
      this.nOldTickleCount = 0;
      this.userProfileMap = null;
      this.bFirstSlide = true;
      this.bPlayerReady = false;
	  this.elLikeBtn = $('.like_btn', $(this.el));
  
      this.bSlideFull = true;
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
            
  	  var nHeaderHeight = $('#headerview').height();
      $(document).bind('ready scroll',function() {
        var docScroll = $(document).scrollTop();
        if(docScroll >= nHeaderHeight) {
          $('#small_sponsor_bar').addClass('fixed');
        } else {
          $('#small_sponsor_bar').removeClass('fixed');
        }
      });      
      
      this.trailStatsView = new TrailStatsView({ el: '#trail_stats_view', model: this.model });
      this.trailAltitudeView = new TrailAltitudeView({ el: '#trail_altitude_view', model: this.model });
      this.trailMiniMapView = new TrailMiniMapView({ el: '#trail_minimap_view', model: this.model });
      this.trailMiniSlidesView = new TrailMiniSlidesView({ el: '#trail_minislides_view', model: this.model });
  
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
      
      this.updatePlayerHeight();
      
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
              self.tickle();      
            }
          });        
        }      
      });      
    },   
    buildBtns: function(){
      var self = this;
      
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

      $('#trail_mini_view .toggle_view_button').mouseover(function(evt){              
        $(evt.currentTarget).css('cursor','pointer');      
      });    

      $('#trail_mini_view .toggle_view_button').click(function(evt){
        self.onTrailToggleViewBtnClick(evt);
      });

      $('#trail_overlay .overlay_pull').click(function(evt){
        $('#trail_overlay .overlay_pull .button').removeClass('overlay_pull_hover');                
        self.toggleOverlay();
      });
      
      $('#trail_overlay .overlay_pull').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');
        $('#trail_overlay .overlay_pull .button').addClass('overlay_pull_hover');                
      });      
      $('#trail_overlay .overlay_pull').mouseout(function(evt){
        $('#trail_overlay .overlay_pull .button').removeClass('overlay_pull_hover');                
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
      var elContentView = $('#content_view');
      var nContentY = elContentView.position().top;
      
      nPlayerHeight = Math.round(elContentView.width() * 0.746875);                  
      // check height fits
      if ((nPlayerHeight+nContentY) > $(window).height()) {  
        nPlayerHeight = $(window).height() - nContentY;
      }
      if (nPlayerHeight < MIN_HEIGHT) {
        nPlayerHeight = MIN_HEIGHT;
      }
      
      // height of white bar
      nPlayerHeight -= 8;
      this.nPlayerHeight = nPlayerHeight;
      if (this.bSlideFull) {
        $('#trailplayer').height(this.nPlayerHeight);            
      }
      else {
        $('#trailplayer').height(this.nPlayerHeight - PLAYER_REDUCE_HEIGHT);            
      }      

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
        this.trailMiniSlidesView.render();
        this.trailStatsView.render();
        this.trailAltitudeView.render();
      }
    },
    handleTrail: function(){
	  $('#trail_views').addClass('tb-move-vert');

      $('#trail_overlay').addClass('tb-move-vert');
      $('#trail_info').addClass('tb-move-vert');
      $('#trail_info .trail_avatar').addClass('tb-move-vert');       
      $('#trail_info .trail_title').addClass('tb-move-vert');            
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
    },
    handleMedia: function(){
      var self = this;
      
      var jsonMedia = this.mediaModel.get('value');
      // add to collection
      $.each(jsonMedia, function(key, media) {
        self.mediaCollection.add(new Backbone.Model(media));      
      });
      // iterate collection
 	  this.mediaCollection.each(function(model) {
        self.trailMiniMapView.addMedia(model);
        self.trailMapView.addMedia(model);
        
        self.trailMiniSlidesView.addMedia(model);
        self.trailSlidesView.addMedia(model);
        
        self.trailAltitudeView.addMedia(model);
        self.trailWeatherView.render();
      });      
      
      this.trailAltitudeView.renderMarkers();
      this.trailMiniMapView.renderMarkers();          
      this.trailMapView.renderMarkers();
          
      this.bPlayerReady = true;          
          
      this.handleResize();      
      this.startSlideShow();
      
      // keyboard control
      $(document).keydown(function(e){
      	switch (e.keyCode) {
      	  case 13: // toggle overlay
            e.preventDefault();
            self.toggleOverlay();
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

      this.showTitle();
    },
    toggleOverlay: function(){
      // add transition for effect
      $('#trailplayer').addClass('tb-size');

      if (this.bFirstSlide) {
        $('#trail_overlay').addClass('delay_transition');
        $('#trail_info').addClass('delay_transition');
        $('#trail_info .trail_avatar').addClass('delay_transition');       
        $('#trail_info .trail_title').addClass('delay_transition');            
        $('#trail_stats_view').addClass('delay_transition');
        $('#trail_altitude_view').addClass('delay_transition');            
        $('#trail_mini_view').addClass('delay_transition');      
      }
      else {
        $('#trail_overlay').removeClass('delay_transition');
        $('#trail_info').removeClass('delay_transition');
        $('#trail_info .trail_avatar').removeClass('delay_transition');       
        $('#trail_info .trail_title').removeClass('delay_transition');            
        $('#trail_stats_view').removeClass('delay_transition');
        $('#trail_altitude_view').removeClass('delay_transition');            
        $('#trail_mini_view').removeClass('delay_transition');      
      }
                        
      if (this.bSlideFull) {
        this.bSlideFull = false;        
        
        this.trailMapView.enablePopups(false);
        
        $('#trail_views').css('top', -(PLAYER_REDUCE_HEIGHT/2));        
        $('#trailplayer').height(this.nPlayerHeight - PLAYER_REDUCE_HEIGHT);
        
        $('#trail_overlay').css('top', -208);

        $('#trail_stats_view').css('top', 0);
        $('#trail_altitude_view').css('top', 0);        
        $('#trail_mini_view').css('top', 0);
      }
      else {
        this.bSlideFull = true;        

        this.trailMapView.enablePopups(true);

        $('#trail_views').css('top', 0);
        $('#trailplayer').height(this.nPlayerHeight);
        
        $('#trail_stats_view').css('top', 40);
        $('#trail_altitude_view').css('top', 60);
        $('#trail_mini_view').css('top', 80);
        
        $('#trail_overlay').css('top', 0);        
      }           
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
      this.trailMiniSlidesView.gotoSlide(nSlide);    
      this.trailSlidesView.gotoSlide(nSlide);
      
      this.trailMiniMapView.gotoMedia(nSlide);
      this.trailMapView.gotoMedia(nSlide);
      
      this.trailAltitudeView.gotoMedia(nSlide);
      
      this.nCurrSlide = nSlide;    
      
      // render next slide to avoid stalling when in slide show
      if (nSlide < this.mediaCollection.length-1) {
        this.trailMiniSlidesView.render(nSlide+1);    
        this.trailSlidesView.render(nSlide+1);
      }        
      
      this.updatePlayerHeight();      
    },
    showTitle: function(){
      if (this.nTitleState != TITLE_OFF) {
        return;
      }
      // only show when in slide view
      if (this.nTrailView != SLIDE_VIEW) {
        return;
      }      
          
      this.nTitleState = TITLE_ON;
      
      $('#trail_info').removeClass('delay_transition');      
      $('#trail_info').css('top', 24);       
      $('#trail_info .trail_avatar').css('top', 0);       
      $('#trail_info .trail_title').css('top', 0);       
    },
    hideTitle: function(){
      if (this.nTitleState != TITLE_ON) {
        return;
      }    
      this.nTitleState = TITLE_OFF;
      
      $('#trail_info').css('top', -300);        
      $('#trail_info .trail_avatar').css('top', -300);       
      $('#trail_info .trail_title').css('top', -100);       
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
    onTickleTimer: function(){
//      console.log("onTickleTimer:"+this.nOldTickleCount+' : '+this.nTickleCount);
      if (this.nOldTickleCount == this.nTickleCount) {
        this.hideTitle();
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
          this.nTrailView = SLIDE_VIEW;
          
          $('#view_toggle .button').addClass('view_map');
          $('#view_toggle .button').removeClass('view_photo');
          if (evt) {
            if (!Modernizr.touch && $(evt.currentTarget).attr('id') == 'view_toggle_btn') {
              $('#view_toggle .button').addClass('view_map_hover');        
            }
          }
                    
          this.trailMiniSlidesView.hide();
          this.trailMiniMapView.show();
          this.trailMiniMapView.render();
          
          this.trailMapView.hide();
          this.trailSlidesView.show();
          this.trailSlidesView.render();
          
          this.showTitle();
          break;
          
        case SLIDE_VIEW:
          this.nTrailView = MAP_VIEW;
          
          $('#view_toggle .button').addClass('view_photo');
          $('#view_toggle .button').removeClass('view_map');
          if (evt) {
            if (!Modernizr.touch && $(evt.currentTarget).attr('id') == 'view_toggle_btn') {
              $('#view_toggle .button').addClass('view_photo_hover');
            }        
          }
          
          this.trailMiniMapView.hide();
          this.trailMiniSlidesView.show();
          this.trailMiniSlidesView.render();
          this.trailSlidesView.hide();
          
          this.trailMapView.show();
          this.trailMapView.render();
          
          this.hideTitle();
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
        // show overlay
        this.toggleOverlay();
        this.bFirstSlide = false;
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
