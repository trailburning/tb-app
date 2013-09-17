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

    this.userProfileMap = null;

    // mla - move into view
    this.polyline = null;
    this.arrLineCordinates = [];
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    $('.viewmap-btn').click(function(evt){
      $('#map_large').show();                
      $('#trailplayer_slides').hide();
      handleResize();      
    });    
    
    function handleResize() {
      var nTrailPlayerLeftWidth = $('#trailplayer .left').width();
      var nPlayerPanelWidth = $(window).width() - nTrailPlayerLeftWidth;
      if (nPlayerPanelWidth < (1024 - nTrailPlayerLeftWidth)) {
        nPlayerPanelWidth = (1024 - nTrailPlayerLeftWidth);
      } 
      
      $('#trailplayer .image_container').width(nPlayerPanelWidth);
      $('#trailplayer .map_container').width(nPlayerPanelWidth);
      
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
    
    function handleTrail() {      
      var map = L.mapbox.map('map_large', 'mallbeury.map-omeomj70', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});

      var data = self.trailModel.get('value');      
      $.each(data.route.route_points, function(key, point) {
        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      });

      var polyline_options = {
        color: '#44B6FC',
        opacity: 1,
        weight: 4,
        clickable: false
      };         
      self.polyline = L.polyline(self.arrLineCordinates, polyline_options).addTo(map);          
      map.fitBounds(self.polyline.getBounds(), {padding: [20, 20]});
      
      $('#map_large').hide();                      
    }
    
    // get trail    
    this.trailModel.set('id', 14);    
    console.log('Fetch ID:'+this.trailModel.get('id'));            
    this.trailModel.fetch({
      success: function () {
        console.log('Fetched');
        
        $('#trailplayer .trailview_toggle').show();
        
        handleTrail();
        
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
