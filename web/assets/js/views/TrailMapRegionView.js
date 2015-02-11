define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMapRegionView = Backbone.View.extend({
    initialize: function(){
    },            
    render: function(){
      var self = this;
      
      var jsonPoint = this.model.get('value').route.route_points[0]; 
      var map = L.mapbox.map('trail_location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      
      function onClick(evt) {
      	window.location = $(self.el).attr('data-url'); 
      }
      
      var startIcon = new LocationIcon({iconUrl: 'http://assets.trailburning.com/images/icons/location.png'});
      L.marker([jsonPoint.coords[1], jsonPoint.coords[0]], {icon: startIcon}).on('click', onClick).addTo(map);      

      var latlng = new L.LatLng(jsonPoint.coords[1], jsonPoint.coords[0]);
      map.setView(latlng, 12);
    }
    
  });

  return TrailMapRegionView;
});
