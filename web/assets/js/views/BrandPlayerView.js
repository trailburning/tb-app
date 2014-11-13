define([
  'underscore', 
  'backbone',
  'views/CampaignSlidesView',  
  'views/maps/MapTrailView'
], function(_, Backbone, CampaignSlidesView, MapTrailView){
  
  var PLAYER_INTRO = 0;
  var PLAYER_SHOW = 1;  
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;
  
  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;
  
  var BrandPlayerView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.nPlayerView = PLAYER_INTRO;
	  // do we have a route to select?
	  var nRouteID = $.cookie('route_id');
	  if (nRouteID != undefined) {
        this.nPlayerView = PLAYER_SHOW;
	  }

      this.nTrailView = SLIDE_VIEW;
      this.nSlideShowState = SLIDESHOW_INIT;

      this.bLocked = true;
      this.collection = new Backbone.Collection();
      this.slideTimer = null;
      this.nCurrSlide = -1;
      this.bMapReady = false, this.bSlidesReady = false;
      this.bFirstSlide = true;
      this.bPlayerReady = false;  
      this.bSlideFull = true;

      this.nPlayerHeight = 0;
      this.nPlayerMinHeight = $('#campaignplayer').height();

      app.dispatcher.on("TrailMapView:selecttrail", self.onSelectTrail, this);
      app.dispatcher.on("TrailMapView:zoominclick", self.onTrailMapViewZoomInClick, this);
      app.dispatcher.on("TrailMapView:zoomoutclick", self.onTrailMapViewZoomOutClick, this);
      app.dispatcher.on("TrailSlidesView:slideview", self.onTrailSlidesViewSlideView, this);

      this.trailSlidesView = new CampaignSlidesView({ el: '#trail_slides_view', model: this.mediaModel });
      this.trailMapView = new MapTrailView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
//      this.trailCardView = new TrailCardView({ el: '#trailcard_view' });

	  this.getResults();
	  this.buildBtns();
	  
	  if (this.nPlayerView == PLAYER_INTRO) {
        $('#trail_slides_view').css('visibility', 'visible');
	  }
	  
	  var data = {'tags': {'width': 800, 'height': 600}, versions: [{ 'path': '/images/profile/mtbuller/LM_131122_MtBuller_0462_HIGHres.jpg' }]};
	  
	  var mediaModel = new Backbone.Model(data);
	  this.trailSlidesView.addMedia(mediaModel);
	  
      this.trailSlidesView.gotoSlide(0);
    },
    buildBtns: function(){
      var self = this;
      
      $('#trail_mini_view .toggle_btn').click(function(evt){
        self.onTrailToggleViewBtnClick(evt);
	  });

      $('#trail_mini_view .toggle_btn').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });
      
      $('#campaign_landing_overlay_view .campaign_play').click(function(evt){
      	self.showPlayer();
	  });

      $('#campaign_landing_overlay_view .campaign_play').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });

      $('#view_player_btns .close_btn').click(function(evt){
      	self.hidePlayer();
	  });

      $('#view_player_btns .close_btn').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });	  
	},    
    render: function(){
  	  this.trailMapView.render();        
//	  this.trailCardView.render();
	},
	handleResize: function(){
      // remove transition to avoid seeing grey beneath image when resizing
      $('#campaignplayer').removeClass('tb-size');
      $('#trail_views').removeClass('tb-move-vert');
      
      this.updatePlayerHeight();
      $('#campaignplayer .map_container').width($('#appview').width());
      
      switch (this.nTrailView) {
        case SLIDE_VIEW:
          break;
           
        case MAP_VIEW:
          this.trailMapView.render();
          this.trailMapView.map.zoomOut(4, {animate: false});
          break;
      }      
      
      if (this.bPlayerReady) {
        this.trailSlidesView.render();
      }
	},
    updatePlayerHeight: function(){
      var nImageHeight = 0;      
      var nPlayerViewerHeight = 0;

      var elContentView = $('#content_view');
      var nContentY = elContentView.position().top;
      var nContentHeight = $(window).height() - nContentY;
      
      nImageHeight = Math.round(elContentView.width() * 0.746875);
      
	  switch (this.nPlayerView) {
	  	case PLAYER_INTRO:
	  	  nPlayerViewerHeight = this.nPlayerMinHeight;
	  	  this.nPlayerHeight = nImageHeight;
	  	  
	  	  if (nImageHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nImageHeight - nPlayerViewerHeight)/2;
            $('#trail_views').css('top', -nAdjustY);        	  	  	
	  	  } 
	  	  break;
	  	
	  	case PLAYER_SHOW:
	  	  nPlayerViewerHeight = nContentHeight;
	  	  
	  	  if (nImageHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nImageHeight - nPlayerViewerHeight)/2;
            $('#trail_views').css('top', -nAdjustY);            
            $('#trail_map_view').css('top', nAdjustY);                    	  	  	
		  }
		  
	  	  if (nImageHeight < nPlayerViewerHeight) {
	  	  	// player is smaller than viewer
	  	    nPlayerViewerHeight = nPlayerHeight;
	  	    $('#trail_views').css('top', 0);	  	  	
		  }
		  
      	  this.nPlayerHeight = nImageHeight;
	  	  break;
	  }

      $('#campaignplayer').height(nPlayerViewerHeight);
      
  	  $('#trail_slides_view').height(this.nPlayerHeight);
   	  // force height update for imageScale
   	  $('#trail_slides_view .image_container').height(this.nPlayerHeight);      	  
	  
   	  $('#trail_map_view').height(nContentHeight);
   	  // force height update for MapBox
   	  $('#trail_map_view .map_container').height(nContentHeight);      	  	  
    },    
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
		  		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?order=distance&radius=50&lat=-37.132552&long=146.454196&limit=500&offset=0';	  
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
          var model;
      	  $.each(data.value.routes, function(key, card) {
	    	model = new Backbone.Model(card);
	    	self.trailMapView.addTrail(model);
	    	self.collection.add(model);	    
		  });
		  self.trailMapView.updateTrails();
		  self.bMapReady = true;
		  self.playerCheckpoint();
        }
      });        
    },    
    handleMedia: function(){
      var self = this;
      
      $('#trail_overlay').addClass('tb-move-vert');
      $('#trail_info').addClass('tb-move-vert');
      $('#trail_info .trail_avatar').addClass('tb-move-vert');       
      $('#trail_info .trail_title').addClass('tb-move-vert'); 
      $('#view_player_btns').addClass('tb-move-vert');
      $('#view_map_btns').addClass('tb-move-vert');
      
      this.bPlayerReady = true;
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
      if (this.bLocked) {
    	return;
      }
      this.bLocked = true;
      
	  var self = this;

      // add transition for effect      
      $('#campaignplayer').addClass('tb-size');
	  $('#trail_views').addClass('tb-move-vert');

      this.showMapView();
      
      this.hideIntroOverlay();

      self.nPlayerView = PLAYER_SHOW;
      self.updatePlayerHeight();
	  self.bLocked = false;
            
      $('#view_player_btns').css('top', 18);
    },
    hidePlayer: function(){
      if (this.bLocked) {
    	return;
      }
      this.bLocked = true;
      	
	  var self = this;
      	
      this.nPlayerView = PLAYER_INTRO;

	  $('#headerview .close_link').hide();

      // add transition for effect      
      $('#campaignplayer').addClass('tb-size');
	  $('#trail_views').addClass('tb-move-vert');
      
      this.updatePlayerHeight();
      
      $('#view_player_btns').css('top', -160);

	  self.showPhotoView();      
      self.showIntroOverlay();
      self.bLocked = false;
    },
    showIntroOverlay: function(){
      $('#campaign_landing_overlay_view .back').css('left', -144);
      $('#campaign_landing_overlay_view .info-hero').css('left', -144);
      $('#campaign_landing_overlay_view .info-hero .campaign_title').css('left', 189);                                	          
      
      $('#campaign_map_overlay_view .back').css('left', -800);
      $('#campaign_map_overlay_view .info-hero').css('left', -800);
      $('#campaign_map_overlay_view .info-hero .campaign_title').css('left', -100);
    },
    hideIntroOverlay: function(){    
      $('#campaign_landing_overlay_view .back').css('left', -800);
      $('#campaign_landing_overlay_view .info-hero').css('left', -800);
      $('#campaign_landing_overlay_view .info-hero .campaign_title').css('left', -100);
      
      $('#campaign_map_overlay_view .back').css('left', -124);
      $('#campaign_map_overlay_view .info-hero').css('left', -150);
      $('#campaign_map_overlay_view .info-hero .campaign_title').css('left', 189);                                	          
    },
    toggleView: function(){
      if (this.nPlayerView != PLAYER_SHOW) {
      	return;
      }
    	
      this.onTrailToggleViewBtnClick();
    }, 
    showNextSlide: function(){
      if (this.nPlayerView != PLAYER_SHOW) {
      	return;
      }

      this.stopSlideShow();
	  this.nextSlide();         
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
    showPrevSlide: function(){
      if (this.nPlayerView != PLAYER_SHOW) {
      	return;
      }
    	
      this.stopSlideShow();
	  this.prevSlide();         
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
      this.trailMiniSlidesView.gotoSlide(nSlide);
	  this.trailStatsView.setCurrSlide(nSlide+1);
      
      this.trailMiniMapView.gotoMedia(nSlide);
      this.trailMapView.gotoMedia(nSlide);
      
      this.trailAltitudeView.gotoMedia(nSlide);
      
      this.nCurrSlide = nSlide;    
      
      // render next slide to avoid stalling when in slide show
      if (nSlide < this.mediaCollection.length-1) {            
        this.trailSlidesView.render(nSlide+1);
        this.trailMiniSlidesView.render(nSlide+1);
      }              
      this.updatePlayerHeight();      
    },
    toggleSlideshow: function(){
      if (this.nPlayerView != PLAYER_SHOW) {
      	return;
      }
    	
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
      
      $('#view_map_btns').css('top', 18);
      
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
      this.trailMapView.map.zoomOut(4, {animate: false});      
    },
    showPhotoView: function(evt){
      if (this.nTrailView == SLIDE_VIEW) {
      	return;
      }
    	
      this.nTrailView = SLIDE_VIEW;
      
      $('#view_map_btns').css('top', -300);
      
      $('#view_toggle .button').addClass('view_map');
      $('#view_toggle .button').removeClass('view_photo');
      if (evt) {
        if (!Modernizr.touch && $(evt.currentTarget).attr('id') == 'view_toggle_btn') {
          $('#view_toggle .button').addClass('view_map_hover');        
        }
      }
                          
      this.trailMapView.hide();
      this.trailSlidesView.show();
      this.trailSlidesView.render();
    },
    playerCheckpoint: function(){
      if (!this.bMapReady || !this.bSlidesReady) {
      	return;
      }
      
      this.bLocked = false;
        
      switch (this.nPlayerView) {
        case PLAYER_INTRO:
          this.showIntroOverlay();
          break;
            
        case PLAYER_SHOW:
          this.showPlayer();
          
	  	  var nRouteID = $.cookie('route_id');          
		  if (nRouteID != undefined) {
    	    this.trailMapView.setMapView(new L.LatLng($.cookie('route_lat'), $.cookie('route_lng')), $.cookie('route_zoom'));
            this.trailMapView.selectTrail(nRouteID);
		  	// remove
		  	$.removeCookie('route_id');
		    $.removeCookie('route_lat');
		    $.removeCookie('route_lng');
		    $.removeCookie('route_zoom');        
		  }          
          break;
      }
    },    
    onTrailStatsPlayClick: function(){
      this.startSlideShow(); 
    },
    onTrailStatsPauseClick: function(){   
      this.stopSlideShow(); 
    },
    onTrailMapViewZoomInClick: function(mapView){
    },
    onTrailMapViewZoomOutClick: function(mapView){
    },
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      // look up model in collcetion
      var nMedia = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      this.stopSlideShow();
      this.gotoMedia(nMedia);
    },
    onTrailMapMediaPhotoClick: function(mapMediaMarkerView){
      this.toggleView();
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
      	this.bSlidesReady = true;
      	this.playerCheckpoint();
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
    },
    onSelectTrail: function(id){
      var model = this.collection.get(id);
//	  this.trailCardView.render(model);
    }
    
  });

  return BrandPlayerView;
});
