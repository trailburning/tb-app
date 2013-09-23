define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
            
      this.bRendered = false;
      this.map = null;
      this.polyline = null;
      this.arrLineCordinates = [];
      this.jsonMarkers = null;
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    addMarkers: function(jsonMarkers){
      this.jsonMarkers = jsonMarkers;
    },        
    renderMarkers: function(){
      if (!this.jsonMarkers) {
        return;
      }

      var self = this;
      
      // icons      
      var MediaIcon = L.Icon.extend({
          options: {
              iconSize:     [23, 24],
              iconAnchor:   [11, 11],
              popupAnchor:  [11, 11]
          }
      });      
      var mediaIcon = new MediaIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/marker_inactive.png'});

      $.each(this.jsonMarkers, function(key, point) {
        L.marker([point.coords.lat, point.coords.long], {icon: mediaIcon}).addTo(self.map);      
      });
      
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      var startIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/location.png'});
      L.marker(this.arrLineCordinates[0], {icon: startIcon}).addTo(this.map);            
    },        
    render: function(){
      console.log('TrailMapView:render');
        
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }
       
      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        this.map.fitBounds(this.polyline.getBounds(), {padding: [30, 30]});
        return;         
      }        
                
      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
                        
      this.map = L.mapbox.map('map_large', 'mallbeury.map-omeomj70', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});

      var data = this.model.get('value');      
      $.each(data.route.route_points, function(key, point) {
        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      });

      var polyline_options = {
        color: '#44B6FC',
        opacity: 1,
        weight: 4,
        clickable: false
      };         
      this.polyline = L.polyline(self.arrLineCordinates, polyline_options).addTo(this.map);          
      this.map.fitBounds(self.polyline.getBounds(), {padding: [30, 30]});
                        
      this.renderMarkers();                        
                        
      this.bRendered = true;
                        
      return this;
    }    
  });

  return TrailMapView;
});
