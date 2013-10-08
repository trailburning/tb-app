var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';
//var RESTAPI_BASEURL = 'http://localhost:8888/api/';

// weather
// http://api.openweathermap.org/data/2.5/weather?q=Berlin,Germany&units=metric

define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'models/TrailMediaModel',
  'views/TrailMiniMapView',
  'views/TrailMiniSlideView',
  'views/TrailSlideView',  
  'views/TrailMapView',  
  'views/TrailAltitudeView'  
], function(_, Backbone, TrailModel, TrailMediaModel, TrailMiniMapView, TrailMiniSlideView, TrailSlideView, TrailMapView, TrailAltitudeView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var MIN_WIDTH = 1160;
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;

  var HOLD_SLIDE = 8000;

  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;
  
  var initialize = function() {
    var self = this;
    
    app.dispatcher.on("TrailSlideView:slideview", onTrailSlideViewSlideView, this);
    
    this.nTrailView = SLIDE_VIEW;
    this.nSlideShowState = SLIDESHOW_INIT;
    this.slideTimer = null;
    this.nCurrSlide = -1;
    this.mediaCollection = new Backbone.Collection();
    
    this.trailModel = new TrailModel();
    this.mediaModel = new TrailMediaModel();

    this.trailMiniMapView = new TrailMiniMapView({ el: '#trail_minimap_view', model: this.trailModel });

    this.trailSlideView = new TrailSlideView({ el: '#trail_slide_view', model: this.mediaModel });
    this.trailMapView = new TrailMapView({ el: '#trail_map_view', model: this.trailModel });
    
    this.trailAltitudeView = new TrailAltitudeView({ el: '#trailaltitudeview', model: this.trailModel });

    this.userProfileMap = null;

    bSlideFull = true;

    $('#overlay_toggle .overlay_toggle_btn').click(function(evt){      
      toggleSlide($(evt.currentTarget));
    });

    $('#overlay_toggle .overlay_toggle_btn').mouseover(function(evt){              
      $(evt.currentTarget).css('cursor','pointer');      
    });    

    $(window).resize(function() {
      handleResize(); 
    });    

    function toggleSlide(elBtn) {
      if (bSlideFull) {
        bSlideFull = false;
        $('#trail_slide_view .photos_container').css('top', -50);
        $('#trailplayer').height(650);
        $('#trail_overlay').css('top', 650 - 218);
      }
      else {
        bSlideFull = true;        
        $('#trail_slide_view .photos_container').css('top', 0);
        $('#trailplayer').height(600);
        $('#trail_overlay').css('top', 700);
      }     
    }

    function startSlideShow() {    
      self.nSlideShowState = SLIDESHOW_PLAYING;
          
      nextSlide();
    }
    
    function stopSlideShow() {    
      self.nSlideShowState = SLIDESHOW_STOPPED;
      
      if (self.slideTimer) {
        clearTimeout(self.slideTimer);
      }
    }    

    function nextSlide(){    
      var nSlide = self.nCurrSlide; 
      if (nSlide < self.mediaCollection.length-1) {
        nSlide++;                               
      }
      else {
        nSlide = 0;
      }
      gotoMedia(nSlide);
    }    

    function gotoMedia(nSlide){
      self.trailSlideView.gotoSlide(nSlide);
      
      self.trailMiniMapView.gotoMedia(nSlide);
      self.trailMapView.gotoMedia(nSlide);
      
      self.trailAltitudeView.gotoMedia(nSlide);
      
      self.nCurrSlide = nSlide;      
    }

    function onTrailSlideViewSlideView() {
      // start timer
      if (self.slideTimer) {
        clearTimeout(self.slideTimer);
      }
      
      if (self.nSlideShowState == SLIDESHOW_PLAYING) {
        self.slideTimer = setTimeout(function() {
          onShowNextSlide();
        }, HOLD_SLIDE);
      }
    }    

    function onShowNextSlide() {
      console.log('onShowNextSlide');
      
      nextSlide();          
    }
    
    function handleResize() {
      var nTrailPlayerLeftWidth = $('#trailplayer .left').width();
      var nTrailPlayerRightWidth = $(window).width() - nTrailPlayerLeftWidth;

      if (nTrailPlayerRightWidth < (MIN_WIDTH - nTrailPlayerLeftWidth)) {
        nTrailPlayerRightWidth = (MIN_WIDTH - nTrailPlayerLeftWidth);
      } 
      
      switch (self.nTrailView) {
        case SLIDE_VIEW:
          self.trailSlideView.render($(window).width());
          break;
           
        case MAP_VIEW:
          self.trailMapView.render();
          break;
      }      
      self.trailAltitudeView.render();
      
      $('.image').resizeToParent();
    }        
        
    function handleMedia() {
      var jsonMedia = self.mediaModel.get('value');
      $.each(jsonMedia, function(key, media) {
        var mediaModel = new Backbone.Model(media);        
        self.mediaCollection.add(mediaModel);      
                
        self.trailMiniMapView.addMedia(mediaModel);
        self.trailMapView.addMedia(mediaModel);
        
        self.trailSlideView.addMedia(mediaModel);
        
        self.trailAltitudeView.addMedia(mediaModel);
      });
      self.trailAltitudeView.renderMarkers();
      self.trailMiniMapView.renderMarkers();          
      self.trailMapView.renderMarkers();
          
      handleResize();      
      startSlideShow();
    }
    
    // get trail    
    this.trailModel.set('id', 44); 
    console.log('Fetch ID:'+this.trailModel.get('id'));            
    this.trailModel.fetch({
      success: function () {
        console.log('Fetched');
                               
        self.trailMiniMapView.render();
        self.trailMapView.render();

        self.mediaModel.url = RESTAPI_BASEURL + 'v1/route/'+self.trailModel.get('id')+'/medias';
        self.mediaModel.fetch({
          success: function () {
            console.log('Fetched media');
              
            handleMedia(self.mediaModel);
          }
        });        
      }      
    });    
  };
    
  return { 
    initialize: initialize
  };   
});  
