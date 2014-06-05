define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var ProfileMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#profileMapViewTemplate').text());        
      
      this.userProfileMap = null;
    },            
    render: function(){
      var self = this;
      
      $(this.el).html(this.template());
      // no pointer events so do not show overlay      
      if (Modernizr.pointerevents) {
        $('#profile_map_overlay').show();
      }
      
      this.userProfileMap = L.mapbox.map('profile_map', 'mallbeury.test', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});      
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
          
      arrMarkers.push([TB_USER_LONG, TB_USER_LAT]);                   
      L.marker(arrMarkers[arrMarkers.length-1], {icon: locationIcon, zIndexOffset: 1000}).on('click', onClick).addTo(this.userProfileMap);

      var bounds = new L.LatLngBounds(arrMarkers);
      bounds = bounds.pad(0.05);
      this.userProfileMap.fitBounds(bounds);
    }    
  });

  return ProfileMapView;
});
