var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';
//var RESTAPI_BASEURL = 'http://localhost:8888/api/';

//https://s3-eu-west-1.amazonaws.com/trailburning-media/af86ca5c0885dfda4a174aa5ed08a33d408087e0.jpg

define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'models/TrailMediaModel',
  'views/TrailMapView',
  'views/TrailAltitudeView'  
], function(_, Backbone, TrailModel, TrailMediaModel, TrailMapView, TrailAltitudeView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    this.trailModel = new TrailModel();
    this.mediaModel = new TrailMediaModel();

    this.trailMapView = new TrailMapView({ el: '#trailmapview', model: this.trailModel });
    this.trailAltitudeView = new TrailAltitudeView({ el: '#trailaltitudeview', model: this.trailModel });
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    function handleResize() {
      var nDetailContainerWidth = $('.detail1_container').width();
      var nPlayerPanelWidth = $(window).width() - nDetailContainerWidth - 20;
      if (nPlayerPanelWidth < 620) {
        nPlayerPanelWidth = 620;
      } 
      
      $('.trailplayer_panel').width(nPlayerPanelWidth);
      $('.trailplayer_panel .imageContainer').width(nPlayerPanelWidth - 10);      
      $('.image').resizeToParent();
            
      self.trailMapView.update();
      self.trailAltitudeView.update();

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
//    this.trailModel.set('id', 2);    
    this.trailModel.set('id', 15);    
    console.log('Fetch ID:'+this.trailModel.get('id'));            
    this.trailModel.fetch({
      success: function () {
        console.log('Fetched');
        self.trailMapView.render();
        self.trailAltitudeView.render();
        
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
