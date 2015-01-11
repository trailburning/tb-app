define([
  'underscore', 
  'backbone',
  'views/TrailSliderView',    
  'views/TrailMapView',  
  'views/TrailStatsView',  
  'views/TrailAltitudeView'  
], function(_, Backbone, TrailSliderView, TrailMapView, TrailStatsView, TrailAltitudeView){
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;

  var HOLD_SLIDE = 5000;
  
  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;
  
  var TrailPlayerView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.nTrailView = SLIDE_VIEW;

      this.bLocked = true;
      this.slideTimer = null;
      this.nCurrSlide = -1;
      this.nTickleCount = 0;
      this.nOldTickleCount = 0;
      this.userProfileMap = null;
      this.bPlayerReady = false;
  
	  this.mediaCollection = this.options.mediaCollection;
	  this.mediaModel = this.options.mediaModel;

      app.dispatcher.on("TrailStatsView:clickplay", self.onTrailStatsPlayClick, this);
      app.dispatcher.on("TrailStatsView:clickpause", self.onTrailStatsPauseClick, this);
      app.dispatcher.on("TrailMapView:zoominclick", self.onTrailMapViewZoomInClick, this);
      app.dispatcher.on("TrailMapView:zoomoutclick", self.onTrailMapViewZoomOutClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:mediaclick", self.onTrailMapMediaMarkerClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:photoclick", self.onTrailMapMediaPhotoClick, this);      
      app.dispatcher.on("TrailMediaMarkerView:mediaclick", self.onTrailMediaMarkerClick, this);
      app.dispatcher.on("TrailSliderView:ready", self.onTrailSliderReady, this);
      app.dispatcher.on("TrailSliderView:slidemoving", self.onTrailSlideMoving, this);

      this.trailStatsView = new TrailStatsView({ el: '#trail_stats_view', model: this.model });
      this.trailAltitudeView = new TrailAltitudeView({ el: '#trail_altitude_view', model: this.model });
      this.trailSliderView = new TrailSliderView({ el: '.royalSlider', model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });                  
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });

	  $('#trail_map_view').addClass('mini');
	  $('#trail_view .trail_grad_back').show();

	  this.buildBtns();
    },
    buildBtns: function(){
      var self = this;
      
      $('#trail_mini_view .toggle_btn').click(function(evt){
        self.onTrailToggleViewBtnClick(evt);
	  });

      $('#trail_mini_view .toggle_btn').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
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
    render: function(){
  	  this.trailMapView.render();
	},
	handleResize: function(){
	  this.trailSliderView.render();
	  // update map based on how the map is being displayed.
	  if ($('#trail_map_view').css('float') == 'none') {
	  	this.trailMapView.setView(false);
	  }
	  else {
	  	this.trailMapView.setView(true);
	  }
	},
    handleMedia: function(){
      var self = this;
      
      $('#view_map_btns').addClass('tb-move-vert');
      
      var model;
      // set hero slide if we have one
      if (this.model.get('value').route.media) {
      	model = new Backbone.Model(this.model.get('value').route.media);
      	this.trailSliderView.addSlide(model);
      }      
      
      var jsonMedia = this.mediaModel.get('value');
      // add to collection
      $.each(jsonMedia, function(key, media) {
	    model = new Backbone.Model(media);
        self.mediaCollection.push(model);
      });
      
      // iterate collection
 	  this.mediaCollection.each(function(model) {
        self.trailMapView.addMedia(model);
        self.trailAltitudeView.addMedia(model);
      });      
      
      this.trailAltitudeView.render();
      this.trailMapView.renderMarkers();
      this.trailSliderView.render();
      
      this.trailStatsView.render();
	  this.trailStatsView.setTotalSlides(this.mediaCollection.length);
                      
	  // start with hero slide
	  this.nCurrSlide = 0;
      
	  $('.toggleIcn.map').show();
	  $('.toggleBtn').click(function(evt){
	  	if ($('#trail_map_view').hasClass('mini')) {
	  	  $('.toggleIcn.map').hide();
	  	  $('.toggleIcn.photo').show();
	  	  
	  	  $('#trail_map_view').removeClass('mini');
	  	  $('.royalSlider').addClass('mini');
	  	  $('#trail_stats_view').hide();
	  	  $('.trail_grad_back').hide();
	  	  self.trailSliderView.render();
	  	  self.trailMapView.setView(true);	  	    	  	
	  	}
	  	else {
	  	  $('.toggleIcn.photo').hide();
	  	  $('.toggleIcn.map').show();
	  	  
  	      $('#trail_map_view').addClass('mini');
  	      $('.royalSlider').removeClass('mini');
  	      $('#trail_stats_view').show();
  	      $('.trail_grad_back').show();
  	      self.trailSliderView.render();
  	      self.trailMapView.setView(false);
	  	}	  	
	  });
      
      this.bPlayerReady = true;
    },        
    toggleFullscreen: function(){
      this.trailSliderView.toggleFullscreen();
    },
    startSlideShow: function(){
      this.nSlideShowState = SLIDESHOW_PLAYING;

	  this.trailStatsView.playerPlaying();
	  
//      $('#slideshow_toggle .button').addClass('slideshow_pause');
//      $('#slideshow_toggle .button').removeClass('slideshow_play');
          
      this.nextSlide();
    },
    stopSlideShow: function(){
      this.nSlideShowState = SLIDESHOW_STOPPED;

	  this.trailStatsView.playerStopped();
      
//      $('#slideshow_toggle .button').addClass('slideshow_play');
//      $('#slideshow_toggle .button').removeClass('slideshow_pause');
      
      if (this.slideTimer) {
        clearTimeout(this.slideTimer);
      }      
    }, 
    gotoMedia: function(nSlide){
      this.nCurrSlide = nSlide;    
    	         
      this.trailSliderView.gotoSlide(nSlide+1);
	  this.trailStatsView.setCurrSlide(nSlide+1);
      
      this.trailMapView.gotoMedia(nSlide);
      
      this.trailAltitudeView.gotoMedia(nSlide);
      
	  $('#trail_author_view').removeClass('active');
	  $('#trail_fullscreen_author_view').removeClass('active');
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
      
      $('#trail_minimap_view').css('visibility', 'hidden');

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
      this.trailMapView.enablePopups(true);          
    },
    showPhotoView: function(evt){
      if (this.nTrailView == SLIDE_VIEW) {
      	return;
      }
    	
      this.nTrailView = SLIDE_VIEW;
      
      $('#trail_minimap_view').css('visibility', 'visible');
      
      $('#view_map_btns').css('top', -300);
      
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
    onTrailStatsPlayClick: function(){
      this.startSlideShow(); 
    },
    onTrailStatsPauseClick: function(){
      this.stopSlideShow(); 
    },
    onTrailMapViewZoomInClick: function(mapView){
      this.stopSlideShow();
    },
    onTrailMapViewZoomOutClick: function(mapView){
      this.stopSlideShow();
    },
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      // look up model in collcetion
      var nSlide = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      this.stopSlideShow();
//      this.gotoMedia(nMedia);

      this.nCurrSlide = nSlide;    
    	         
      this.trailSliderView.gotoSlide(nSlide+1);
//	  this.trailStatsView.setCurrSlide(nSlide+1);
      
//      this.trailMapView.gotoMedia(nSlide);
      
//      this.trailAltitudeView.gotoMedia(nSlide);
      
//	  $('#trail_author_view').removeClass('active');
//	  $('#trail_fullscreen_author_view').removeClass('active');
    },
    onTrailMapMediaPhotoClick: function(mapMediaMarkerView){
      this.toggleView();
    },
    onTrailMediaMarkerClick: function(mediaMarkerView){
      // look up model in collcetion
      var nSlide = this.mediaCollection.indexOf(mediaMarkerView.model);
      
      this.stopSlideShow();

	  this.trailSliderView.gotoSlide(nSlide+1);
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
    onTrailSliderReady: function(){
      $('#trail_author_view').addClass('active');
	  $('#trail_fullscreen_author_view').addClass('active');
		
	  $('#trail_map_view').addClass('active');
	  $('#trail_stats_view').removeClass('active');
	},
    onTrailSlideMoving: function(strType){
      var nSlide = 0;
      
	  switch (strType) {
	    case 'next':
          nSlide = this.nCurrSlide + 1;	
	      break;
	    case 'prev':
	      nSlide = this.nCurrSlide - 1;
	      break;
	    default:
	      nSlide = strType; // can be a slide number from royalslider
	      break;
	  }
	  
      if (nSlide > this.mediaCollection.length) {
      	nSlide = 0;
	  }
	  else if (nSlide < 0) {
	  	nSlide = this.mediaCollection.length;
	  }
	  this.nCurrSlide = nSlide;
      if (this.nCurrSlide == 0) {
		$('#trail_author_view').addClass('active');
		$('#trail_fullscreen_author_view').addClass('active');
		
	    $('#trail_map_view').addClass('active');
		$('#trail_stats_view').removeClass('active');
        this.trailMapView.reset();            	
        this.trailAltitudeView.reset();            	
 	  }
 	  else {
	    $('#trail_author_view').removeClass('active');
	    $('#trail_fullscreen_author_view').removeClass('active');
	    
	    $('#trail_stats_view').addClass('active');	      
		this.trailStatsView.setCurrSlide(this.nCurrSlide);
        this.trailMapView.gotoMedia(this.nCurrSlide-1);            	
        this.trailAltitudeView.gotoMedia(this.nCurrSlide-1);            	
 	  }    	
    }   
    
/*    
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
        this.bLocked = false;
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
*/    
    
    
  });

  return TrailPlayerView;
});
