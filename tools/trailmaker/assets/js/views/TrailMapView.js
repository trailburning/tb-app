define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaMarkerView'    
], function(_, Backbone, TrailMapMediaMarkerView){

  var TrailMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
      
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];      
      this.arrMapMediaViews = [];
    },            
    render: function(){
      var self = this;
      
      $(this.el).html(this.template());
      // no pointer events so do not show overlay      
      if (Modernizr.pointerevents) {
        $('#profile_map_overlay').show();
      }
      
      this.map = L.mapbox.map('trail_map', 'mallbeury.map-omeomj70', {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:true, zoomAnimation:false, attributionControl:false});      

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
      
      var self = this;
      function onClickTrail(e) {        
        var trailMapMediaMarkerView = new TrailMapMediaMarkerView({ model: self.model, map: self.map, latlng: e.latlng, timezoneData: self.options.timezoneData });
        trailMapMediaMarkerView.render();
        self.arrMapMediaViews.push(trailMapMediaMarkerView);        
      }

      var data = this.model.get('value');      
      $.each(data.route.route_points, function(key, point) {
        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      });

      var polyline_options = {
        color: '#44B6FC',
        opacity: 1,
        weight: 4,
        clickable: true
      };         
      this.polyline = L.polyline(self.arrLineCordinates, polyline_options).on('click', onClickTrail).addTo(this.map);          
      this.map.fitBounds(self.polyline.getBounds(), {padding: [30, 30]});
      
    }    
  });

  return TrailMapView;
});
