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
    
    app.dispatcher.on("TrailMiniMapView:viewbtnclick", onTrailMiniMapViewBtnClick, this);
    app.dispatcher.on("TrailMiniMapView:mediaclick", onTrailMiniMapMediaClick, this);
    app.dispatcher.on("TrailMiniSlideView:viewbtnclick", onTrailMiniSlideViewBtnClick, this);
    app.dispatcher.on("TrailSlideView:slideview", onTrailSlideViewSlideView, this);
    
    this.nTrailView = SLIDE_VIEW;
    this.nSlideShowState = SLIDESHOW_INIT;
    this.slideTimer = null;
    this.nCurrSlide = -1;
    this.mediaCollection = new Backbone.Collection();
    
    this.trailModel = new TrailModel();
    this.mediaModel = new TrailMediaModel();

    this.trailMiniMapView = new TrailMiniMapView({ el: '#trail_minimap_view', model: this.trailModel });
    this.trailMiniSlideView = new TrailMiniSlideView({ el: '#trail_minislide_view', model: this.trailModel });

    this.trailSlideView = new TrailSlideView({ el: '#trail_slide_view', model: this.mediaModel });
    this.trailMapView = new TrailMapView({ el: '#trail_map_view', model: this.trailModel });
    
    this.trailAltitudeView = new TrailAltitudeView({ el: '#trailaltitudeview', model: this.trailModel });

    this.userProfileMap = null;
    
    $(window).resize(function() {
      handleResize(); 
    });    

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
      self.trailMiniSlideView.gotoSlide(nSlide);    
      self.trailSlideView.gotoSlide(nSlide);
      
      self.trailMiniMapView.gotoMedia(nSlide);
      self.trailMapView.gotoMedia(nSlide);
      
      self.trailAltitudeView.gotoMedia(nSlide);
      
      self.nCurrSlide = nSlide;      
    }

    function onTrailMiniMapMediaClick() {
      stopSlideShow();
      // mla - test
      gotoMedia(0);
    }

    function onTrailMiniMapViewBtnClick() {
      self.nTrailView = MAP_VIEW;
      
      self.trailMiniMapView.hide();
      self.trailMiniSlideView.show();
      self.trailMiniSlideView.render();
      
      self.trailSlideView.hide();
      self.trailMapView.show();
      self.trailMapView.render();
      
      handleResize();      
    }

    function onTrailMiniSlideViewBtnClick() {
      self.nTrailView = SLIDE_VIEW;
      
      self.trailMiniSlideView.hide();
      self.trailMiniMapView.show();
      self.trailMiniMapView.render();
      
      self.trailMapView.hide();
      self.trailSlideView.show();
      self.trailSlideView.render();
      
      handleResize();      
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
      $('#trailplayer .right').width(nTrailPlayerRightWidth);
      
      switch (self.nTrailView) {
        case SLIDE_VIEW:
          self.trailSlideView.render(nTrailPlayerRightWidth);
          break;
           
        case MAP_VIEW:
          $('#trailplayer .map_container').width(nTrailPlayerRightWidth);
          self.trailMiniSlideView.render();
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
        
        self.trailMiniSlideView.addMedia(mediaModel);
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
    this.trailModel.set('id', 35); // Ultraks 16k 
//    this.trailModel.set('id', 33); // Aira Force - Lake District 
//    this.trailModel.set('id', 32);
//    this.trailModel.set('id', 30);    
//    this.trailModel.set('id', 17);            
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
