var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';

define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.userProfileMap = null;
        
    $(window).resize(function() {
      handleResize(); 
    });    
    buildProfileMap();
    handleResize();
    
    function handleResize() {
      $('.image').resizeToParent();      
    }
    
    function buildProfileMap() {
      var self = this;
      
      this.userProfileMap = L.mapbox.map('profile_map', 'mallbeury.test', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      this.userProfileMap.on('zoomend', function(e) {
        if (self.userProfileMap.getZoom() > 5) {
          self.userProfileMap.setZoom(5);
        }
      });

      L.Control.Command = L.Control.extend({
      options: {
          position: 'topleft',
      },
      onAdd: function (map) {
        var controlDiv = L.DomUtil.create('div', 'leaflet-overlay');
        
        return controlDiv;
      }
      });
      
      L.control.command = function (options) { return new L.Control.Command(options); };
      
      var overlay = L.control.command();
      overlay.addTo(this.userProfileMap);
      
      var trailIcon = L.icon({
          iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/marker_inactive.png',
          iconSize:     [23, 24],
          iconAnchor:   [10, 10]
      });
      
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      var locationIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/location.png'});
      
      var arrMarkers = [];
          
      arrMarkers.push([54.57269115373492, -2.9278992768377066]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: trailIcon}).addTo(this.userProfileMap);

      arrMarkers.push([52.5080060, 13.2574370]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: locationIcon}).addTo(this.userProfileMap);

      var bounds = new L.LatLngBounds(arrMarkers);
      bounds = bounds.pad(0.05);
      this.userProfileMap.fitBounds(bounds);       
    }    
  };
    
  return { 
    initialize: initialize
  };   
});  
