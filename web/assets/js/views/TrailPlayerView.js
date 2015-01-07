define([
  'underscore', 
  'backbone',
  'views/TrailMiniMapView',
  'views/TrailSliderView',    
  'views/TrailMapView',  
  'views/TrailStatsView',  
  'views/TrailAltitudeView'  
], function(_, Backbone, TrailMiniMapView, TrailSliderView, TrailMapView, TrailStatsView, TrailAltitudeView){
  
  var PLAYER_INTRO = 0;
  var PLAYER_SHOW = 1;  
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;

  var HOLD_SLIDE = 5000;
  
  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;
  
  var TrailPlayerView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.nPlayerView = PLAYER_INTRO;
      this.nTrailView = SLIDE_VIEW;
      this.nSlideShowState = SLIDESHOW_INIT;

      this.bLocked = true;
      this.slideTimer = null;
      this.nCurrSlide = -1;
      this.nTickleCount = 0;
      this.nOldTickleCount = 0;
      this.userProfileMap = null;
      this.bFirstSlide = true;
      this.bPlayerReady = false;
  
      this.bSlideFull = true;

      this.nPlayerHeight = 0;
      this.nPlayerMinHeight = $('#trailplayer').height();
      this.nAvatarWidth = 0;
      if ($('#trail_intro_view .trail_avatar').length) {
      	this.nAvatarWidth = $('#trail_intro_view .trail_avatar').width();
      }

	  this.mediaCollection = this.options.mediaCollection;
	  this.mediaModel = this.options.mediaModel;

      app.dispatcher.on("TrailStatsView:clickplay", self.onTrailStatsPlayClick, this);
      app.dispatcher.on("TrailStatsView:clickpause", self.onTrailStatsPauseClick, this);
      app.dispatcher.on("TrailMapView:zoominclick", self.onTrailMapViewZoomInClick, this);
      app.dispatcher.on("TrailMapView:zoomoutclick", self.onTrailMapViewZoomOutClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:mediaclick", self.onTrailMapMediaMarkerClick, this);
      app.dispatcher.on("TrailMapMediaMarkerView:photoclick", self.onTrailMapMediaPhotoClick, this);      
      app.dispatcher.on("TrailMediaMarkerView:mediaclick", self.onTrailMediaMarkerClick, this);
      app.dispatcher.on("TrailSliderView:slidechanged", self.onTrailSlideChanged, this);

//      app.dispatcher.on("TrailSlidesView:slideview", self.onTrailSlidesViewSlideView, this);
//      app.dispatcher.on("TrailSlidesView:clickslideprev", self.onTrailSlidesViewSlideClickPrev, this);
//      app.dispatcher.on("TrailSlidesView:clickslidenext", self.onTrailSlidesViewSlideClickNext, this);

      this.trailStatsView = new TrailStatsView({ el: '#trail_stats_view', model: this.model });
      this.trailAltitudeView = new TrailAltitudeView({ el: '#trail_altitude_view', model: this.model });
      this.trailMiniMapView = new TrailMiniMapView({ el: '#trail_minimap_view', model: this.model });
//      this.trailMiniSlidesView = new TrailMiniSlidesView({ el: '#trail_minislides_view', model: this.model });  
      this.trailSliderView = new TrailSliderView({ el: '.royalSlider', model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });                  
//      this.trailSlidesView = new TrailSlidesView({ el: '#trail_slides_view', model: this.mediaModel });
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });

	  this.buildBtns();
    },
    buildBtns: function(){
      var self = this;
      
      $('#player_big_btn').click(function(evt){
      	self.showPlayer();
	  });

      $('#player_big_btn').mouseover(function(evt){
      	$(evt.currentTarget).css('cursor','pointer');
	  });
      
      $('#trail_mini_view .toggle_btn').click(function(evt){
        self.onTrailToggleViewBtnClick(evt);
	  });

      $('#trail_mini_view .toggle_btn').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });
      
      $('#trail_intro_view .trail_play').click(function(evt){
      	self.showPlayer();
	  });

      $('#trail_intro_view .trail_play').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });

      $('#view_player_btns .close_btn').click(function(evt){
      	self.hidePlayer();
	  });

      $('#view_player_btns .close_btn').mouseover(function(evt){
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
	  this.trailMiniMapView.render();
  	  this.trailMapView.render();
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
//        this.trailSlidesView.render();
//        this.trailMiniSlidesView.render();        
        this.trailStatsView.render();
        this.trailAltitudeView.render();
      }
	},
    updatePlayerHeight: function(){
      var nPlayerHeight = 0;      
      var nPlayerViewerHeight = 0;

      var elContentView = $('#content_view');
      var nContentY = elContentView.position().top;
      
      nPlayerHeight = Math.round(elContentView.width() * 0.746875);
      
      return;
      
	  switch (this.nPlayerView) {
	  	case PLAYER_INTRO:
	  	  nPlayerViewerHeight = this.nPlayerMinHeight;
	  	  this.nPlayerHeight = nPlayerHeight;
	  	  
	  	  if (nPlayerHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nPlayerHeight - nPlayerViewerHeight)/2;
//            $('#trail_views').css('top', -nAdjustY);        	  	  	
	  	  } 
	  	  break;
	  	
	  	case PLAYER_SHOW:
	  	  nPlayerViewerHeight = 586;
	  	  $('.royalSlider').height(586);
	  	  this.trailSliderView.render();
	  	  
	  	  if (nPlayerHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nPlayerHeight - nPlayerViewerHeight)/2;
//            $('#trail_views').css('top', -nAdjustY);        	  	  	
		  }
		  
	  	  if (nPlayerHeight < nPlayerViewerHeight) {
	  	  	// player is smaller than viewer
	  	    nPlayerViewerHeight = nPlayerHeight;
//	  	    $('#trail_views').css('top', 0);	  	  	
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
    handleMedia: function(){
      var self = this;
      
      $('#trail_overlay').addClass('tb-move-vert');
      $('#trail_info').addClass('tb-move-vert');
      $('#trail_info .trail_avatar').addClass('tb-move-vert');       
      $('#trail_info .trail_title').addClass('tb-move-vert'); 
      $('#view_player_btns').addClass('tb-move-vert');
      $('#view_map_btns').addClass('tb-move-vert');
      $('#trail_stats_view').addClass('tb-move-vert');
      $('#trail_altitude_view').addClass('tb-move-vert');            
      $('#trail_mini_view').addClass('tb-move-vert');      
      
      // set hero slide if we have one
      if (this.model.get('value').route.media) {
//      	this.trailSlidesView.setHeroSlideId(this.model.get('value').route.media.id);
//      	this.trailMiniSlidesView.setHeroSlideId(this.model.get('value').route.media.id);
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
        self.trailAltitudeView.addMedia(model);
//        self.trailSlidesView.addMedia(model);
//        self.trailMiniSlidesView.addMedia(model);
      });      
      
      this.trailStatsView.render();
	  this.trailStatsView.setTotalSlides(this.mediaCollection.length);
      
      this.trailSliderView.render();
      
      this.trailMiniMapView.renderMarkers();          
      this.trailMapView.renderMarkers();
                
	  // start with hero slide
//	  this.nCurrSlide = this.trailSlidesView.getHeroSlide();
	  this.nCurrSlide = 0;
//      this.trailSlidesView.gotoSlide(this.nCurrSlide);
//      this.trailMiniSlidesView.gotoSlide(this.nCurrSlide);
	  this.trailStatsView.setCurrSlide(this.nCurrSlide+1);
      
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

	  $('#player_big_btn').hide();
      // add transition for effect      
//      $('#trailplayer').addClass('tb-size');
//	  $('#trail_views').addClass('tb-move-vert');

	  // is the hero slide not the 1st slide?
	  if (this.nCurrSlide != 0) {
	    this.nCurrSlide = -1;	
	    
	    // change slide to 1st
	    self.startSlideShow();

        setTimeout(function() {
          self.nPlayerView = PLAYER_SHOW;      
          self.updatePlayerHeight();
          
 		  self.hideIntroOverlay();
      	  $('#trail_slides_view .slide_btns').show();
          $('#view_player_btns').css('top', 18);

		  setTimeout(function() {
	    	self.showOverlay();
	    	self.bLocked = false;
	      }, 500);
      	}, 500);	    
	  }
	  else {
        this.nPlayerView = PLAYER_SHOW;
        this.updatePlayerHeight();
        
        this.hideIntroOverlay();
      	$('#trail_slides_view .slide_btns').show();
        $('#view_player_btns').css('top', 18);
      
      	this.nSlideShowState = SLIDESHOW_PLAYING;

	  	this.trailStatsView.playerPlaying();
		
        this.trailMiniMapView.gotoMedia(this.nCurrSlide);
        this.trailMapView.gotoMedia(this.nCurrSlide);
        this.trailAltitudeView.gotoMedia(this.nCurrSlide);
      
        setTimeout(function() {
	      self.showOverlay();
	      self.bLocked = false;
        }, 500);
            
        this.slideTimer = setTimeout(function() {
          self.startSlideShow();
        }, HOLD_SLIDE);      	  	
	  }	  
    },
    hidePlayer: function(){
      if (this.bLocked) {
    	return;
      }
      this.bLocked = true;
      	
	  var self = this;
      	
      this.nPlayerView = PLAYER_INTRO;

	  $('#player_big_btn').show();
	  $('#headerview .close_link').hide();

      // add transition for effect      
//      $('#trailplayer').addClass('tb-size');
//	  $('#trail_views').addClass('tb-move-vert');
      
      $('#trail_slides_view .slide_btns').hide();
      
      this.updatePlayerHeight();
      
      this.stopSlideShow();
      
      $('#view_player_btns').css('top', -160);

      this.hideOverlay();
//      this.trailSlidesView.gotoHeroSlide();
//      this.trailMiniSlidesView.gotoHeroSlide();
//      this.nCurrSlide = self.trailSlidesView.getHeroSlide();
	  this.trailStatsView.setCurrSlide(this.nCurrSlide+1);
      
      setTimeout(function() {      
		self.showPhotoView();      
    	self.showIntroOverlay();
        self.bLocked = false;
      }, 500);
    },
    showIntroOverlay: function(){    
      $('#trail_intro_view .info-hero').css('left', 0);
      $('#trail_intro_view .info-hero .trail_title').css('left', this.nAvatarWidth + 50);                                	          
    },
    hideIntroOverlay: function(){    
      $('#trail_intro_view .info-hero').css('left', -800);
      $('#trail_intro_view .info-hero .trail_title').css('left', -200);
    },
    showOverlay: function(){
      $('.overlay_background').css('opacity', 1);

      $('#trail_stats_view').css('top', 20);
      $('#trail_altitude_view').css('top', 20);        
      $('#trail_mini_view').css('top', 20);
	},
    hideOverlay: function(){
	  $('.overlay_background').css('opacity', 0);

      $('#trail_stats_view').css('top', 300);
      $('#trail_altitude_view').css('top', 350);
      $('#trail_mini_view').css('top', 400);    
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
//      this.trailSlidesView.gotoSlide(nSlide);
//      this.trailMiniSlidesView.gotoSlide(nSlide);
	  this.trailStatsView.setCurrSlide(nSlide+1);
      
      this.trailMiniMapView.gotoMedia(nSlide);
      this.trailMapView.gotoMedia(nSlide);
      
      this.trailAltitudeView.gotoMedia(nSlide);
      
      this.nCurrSlide = nSlide;    
      
      // render next slide to avoid stalling when in slide show
      if (nSlide < this.mediaCollection.length-1) {            
//        this.trailSlidesView.render(nSlide+1);
//        this.trailMiniSlidesView.render(nSlide+1);
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
      
      $('#trail_minimap_view').css('visibility', 'hidden');
      $('#trail_minislides_view').css('visibility', 'visible');

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
      
      $('#trail_minislides_view').css('visibility', 'hidden');
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
    onTrailSlideChanged: function(nSlide){
      if (this.bFirstSlide) {
        this.bFirstSlide = false;
        
        this.showIntroOverlay();
        this.bLocked = false;
      }
      else {
      	this.nCurrSlide = nSlide;
        this.trailMiniMapView.gotoMedia(this.nCurrSlide);
        this.trailAltitudeView.gotoMedia(this.nCurrSlide);            	
      }
	},
    
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
    onShowNextSlide: function(){
      this.nextSlide();          
    }
    
    
  });

  return TrailPlayerView;
});
