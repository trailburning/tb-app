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

  var HOLD_SLIDE = 8000;
  
  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;
  
  var TrailPlayerView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.nTrailView = SLIDE_VIEW;

      this.bLocked = true;
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
      app.dispatcher.on("TrailSliderView:slidemoving", self.onTrailSliderMoving, this);

      this.trailAltitudeView = new TrailAltitudeView({ el: '#trail_altitude_view', model: this.model });
      this.trailSliderView = new TrailSliderView({ el: '.royalSlider', model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });                  
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });

	  $('#trail_map_view').addClass('mini');
//	  $('#trail_view .trail_grad_back').show();

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
	  	  self.trailSliderView.render();
	  	  self.trailMapView.setView(true);	  	    	  	
	  	}
	  	else {
	  	  $('.toggleIcn.photo').hide();
	  	  $('.toggleIcn.map').show();
	  	  
  	      $('#trail_map_view').addClass('mini');
  	      $('.royalSlider').removeClass('mini');
  	      $('#trail_stats_view').show();
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
    gotoMedia: function(nSlide){
      this.nCurrSlide = nSlide;    
    	         
      this.trailSliderView.gotoSlide(nSlide+1);
	  this.trailStatsView.setCurrSlide(nSlide+1);
      
      this.trailMapView.gotoMedia(nSlide);
      
      this.trailAltitudeView.gotoMedia(nSlide);
      
	  $('#trail_author_view').removeClass('active');
	  $('#trail_fullscreen_author_view').removeClass('active');
    },
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      // look up model in collcetion
      var nSlide = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      this.trailSliderView.gotoSlide(nSlide+1);
    },
    onTrailMapMediaPhotoClick: function(mapMediaMarkerView){
      this.toggleView();
    },
    onTrailMediaMarkerClick: function(mediaMarkerView){
      // look up model in collcetion
      var nSlide = this.mediaCollection.indexOf(mediaMarkerView.model);
      
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
    onTrailSliderReady: function(){
      var self = this;
      
      this.trailStatsView = new TrailStatsView({ el: '#trail_stats_view', model: this.model });
      this.trailStatsView.render();
	  this.trailStatsView.setTotalSlides(this.mediaCollection.length);
      
      $('#trail_author_view').addClass('active');
	  $('#trail_fullscreen_author_view').addClass('active');
		
	  $('#trail_map_view').addClass('active');
	  $('#trail_stats_view').removeClass('active');
	},
    onTrailSliderMoving: function(strType){
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
    },
    onTrailSliderClick: function(){
      this.stopSlideShow();
    }    
    
  });

  return TrailPlayerView;
});
