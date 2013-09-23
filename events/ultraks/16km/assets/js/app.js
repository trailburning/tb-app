var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';
//var RESTAPI_BASEURL = 'http://localhost:8888/api/';

//https://s3-eu-west-1.amazonaws.com/trailburning-media/af86ca5c0885dfda4a174aa5ed08a33d408087e0.jpg

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
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;
  
  var initialize = function() {
    var self = this;
    
    app.dispatcher.on("TrailMiniMapView:viewbtnclick", onTrailMiniMapViewBtnClick, this);
    app.dispatcher.on("TrailMiniSlideView:viewbtnclick", onTrailMiniSlideViewBtnClick, this);
    
    this.nTrailView = SLIDE_VIEW;
    
    this.trailModel = new TrailModel();
    this.mediaModel = new TrailMediaModel();

    this.trailMiniMapView = new TrailMiniMapView({ el: '#trail_minimap_view', model: this.trailModel });
    this.trailMiniSlideView = new TrailMiniSlideView({ el: '#trail_minislide_view', model: this.trailModel });

    this.trailSlideView = new TrailSlideView({ el: '#trail_slide_view', model: this.trailModel });
    this.trailMapView = new TrailMapView({ el: '#trail_map_view', model: this.trailModel });
    
    this.trailAltitudeView = new TrailAltitudeView({ el: '#trailaltitudeview', model: this.trailModel });

    this.userProfileMap = null;

    // mla - move into view
    this.polyline = null;
    this.arrLineCordinates = [];
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        

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

    function handleResize() {
      var nTrailPlayerLeftWidth = $('#trailplayer .left').width();
      var nPlayerPanelWidth = $(window).width() - nTrailPlayerLeftWidth;
      if (nPlayerPanelWidth < (1292 - nTrailPlayerLeftWidth)) {
        nPlayerPanelWidth = (1292 - nTrailPlayerLeftWidth);
      } 

      switch (self.nTrailView) {
        case SLIDE_VIEW:
          $('#trailplayer .image_container').width(nPlayerPanelWidth);
          self.trailSlideView.render();
          break;
           
        case MAP_VIEW:
          $('#trailplayer .map_container').width(nPlayerPanelWidth);
          self.trailMapView.render();
          break;
      }
      
      $('.image').resizeToParent();
      $('.image').show();
    }        
        
    function handleMedia() {
      var data = self.mediaModel.get('value');      
      $.each(data, function(key, point) {
        self.trailAltitudeView.addMediaMarker(point.coords.lat, point.coords.long);        
      });
      self.trailAltitudeView.renderMarkers();
    }
    
    // get trail    
    this.trailModel.set('id', 14);    
    console.log('Fetch ID:'+this.trailModel.get('id'));            
    this.trailModel.fetch({
      success: function () {
        console.log('Fetched');
                
        self.trailMiniMapView.render();
        self.trailAltitudeView.render();
        self.trailSlideView.render();
        
        handleResize();
                
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
