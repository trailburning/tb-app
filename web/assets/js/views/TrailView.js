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

  var MIN_HEIGHT = 300;
  
  var PLAYER_INTRO = 0;
  var PLAYER_SHOW = 1;  
  
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
            
      this.nPlayerView = PLAYER_INTRO;
      this.nTrailView = SLIDE_VIEW;
//      this.nDetailOverlayState = DETAIL_OVERLAY_OFF;
      this.nSlideShowState = SLIDESHOW_INIT;
      this.nPlayerHeight = 0;
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

      $('#trail_intro_view .player_show').click(function(evt){
		self.showPlayer();
      });
     
      $('#view_toggle .button').click(function(evt){
        self.onTrailToggleViewBtnClick(evt);
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
    },
    updatePlayerHeight: function(){
    	// mla
    	
      var nPlayerHeight = 0;      
      var nPlayerViewerHeight = 0;

      var elContentView = $('#content_view');
      var nContentY = elContentView.position().top;
      
      nPlayerHeight = Math.round(elContentView.width() * 0.746875);

	console.log('h:'+nPlayerHeight);
      
	  switch (this.nPlayerView) {
	  	case PLAYER_INTRO:
	  	  nPlayerViewerHeight = MIN_HEIGHT;
	  	  this.nPlayerHeight = nPlayerHeight;
	  	  
	  	  if (nPlayerHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nPlayerHeight - nPlayerViewerHeight)/2;
	  	  	console.log('adjust y:'+nAdjustY);
            $('#trail_views').css('top', -nAdjustY);        	  	  	
	  	  } 
	  	  break;
	  	
	  	case PLAYER_SHOW:
      	  // check height fits
      	  if ((nPlayerHeight+nContentY) > $(window).height()) {  
        	nPlayerHeight = $(window).height() - nContentY;
      	  }
      
      	  if (nPlayerHeight < MIN_HEIGHT) {
        	nPlayerHeight = MIN_HEIGHT;
      	  }
  		console.log('SHOW U:'+nPlayerHeight);
	  	

      	  this.nPlayerHeight = nPlayerHeight;
      
      	  $('#trail_slides_view').height(this.nPlayerHeight);
      	  // force height update for imageScale
      	  $('#trail_slides_view .image_container').height(this.nPlayerHeight);

      	  $('#trail_map_view').height(this.nPlayerHeight);
      	  // force height update for MapBox
      	  $('#trail_map_view .map_container').height(this.nPlayerHeight);      
	  	  break;
	  }
/*      
                        
      
*/      
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
            self.toggleDetailOverlay();
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
    showPlayer: function(){
    	// mla
      this.nPlayerView = PLAYER_SHOW;

      // add transition for effect      
      $('#trailplayer').addClass('tb-size');
	  $('#trail_views').addClass('tb-move-vert');
    	
      this.updatePlayerHeight();
    	console.log('SHOW');
    	
    	
//      $('#trail_views').css('top', 0);
//      $('#trailplayer').height(this.nPlayerHeight);

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
    }
  });

  return AppView;
});
