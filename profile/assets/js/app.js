var app = app || {};

define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    this.userProfileMap = null;
    
    buildProfileMap();
    
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
      
      var greenIcon = L.icon({
          iconUrl: 'assets/icons/marker_inactive.png',
          iconSize:     [23, 24],
          iconAnchor:   [10, 10]
      });
      
      var arrMarkers = [];
          
      arrMarkers.push([46.776910834014416, 9.673530561849475]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: greenIcon}).addTo(this.userProfileMap);

      arrMarkers.push([55.55927837267518, -3.536434667184949]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: greenIcon}).addTo(this.userProfileMap);

//      arrMarkers.push([-34.95064677670598, 138.66099251434207]);                   
//      L.marker(arrMarkers[arrMarkers.length-1], {icon: greenIcon}).addTo(this.userProfileMap);
      
      var bounds = new L.LatLngBounds(arrMarkers);
      bounds = bounds.pad(0.05);
      this.userProfileMap.fitBounds(bounds);   
    }
    
  };
    
  return { 
    initialize: initialize
  };   
});  