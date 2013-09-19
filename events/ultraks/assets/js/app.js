var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';

define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    function handleResize() {
      $('.image').resizeToParent();      
      $('.image').show();
    }
    
    function builtEventMap() {  
      var fLat = 46.021073;
      var fLng = 7.747937;
      
      var map = L.mapbox.map('event_location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});

      var CustomIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [14, 14],
              popupAnchor:  [17, 44]
          }
      });      
      var mediaIcon = new CustomIcon({iconUrl: 'http://www.trailburning.com/assets/images/icons/location.png'});
      L.marker([fLat, fLng], {icon: mediaIcon}).addTo(map);      

      var latlng = new L.LatLng(fLat, fLng);
      map.setView(latlng, 7);
    }
                    
    builtEventMap();
  };
    
  return { 
    initialize: initialize
  };   
});  
