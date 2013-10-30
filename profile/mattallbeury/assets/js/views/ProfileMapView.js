define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var ProfileMapView = Backbone.View.extend({
    initialize: function(){
      this.userProfileMap = null;

    },            
    render: function(){
      var self = this;
      
      this.userProfileMap = L.mapbox.map('profile_map', 'mallbeury.test', {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});      
      this.userProfileMap.on('zoomend', function(e) {
        if (self.userProfileMap.getZoom() > 5) {
          self.userProfileMap.setZoom(5);
        }
      });

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

      function onClick(e) {
      }
          
      arrMarkers.push([54.57269115373492, -2.9278992768377066]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: trailIcon, zIndexOffset: 1000}).on('click', onClick).addTo(this.userProfileMap);

      arrMarkers.push([52.5080060, 13.2574370]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: locationIcon, zIndexOffset: 1000}).on('click', onClick).addTo(this.userProfileMap);

      var bounds = new L.LatLngBounds(arrMarkers);
      bounds = bounds.pad(0.05);
      this.userProfileMap.fitBounds(bounds);
    }    
  });

  return ProfileMapView;
});
