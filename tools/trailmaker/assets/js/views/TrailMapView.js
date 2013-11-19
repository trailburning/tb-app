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
      this.timezoneData = null;
    },            
    setTimeZoneData: function(timezoneData){
      this.timezoneData = timezoneData;
    },
    render: function(){
      var self = this;
      
      $(this.el).html(this.template());
      
      this.map = L.mapbox.map('trail_map', 'mallbeury.map-omeomj70', {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:true, zoomAnimation:false, attributionControl:false});      
      
      if (this.model.get('id')) {
        var self = this;
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
        
        function onClickTrail(e) {        
          var trailMapMediaMarkerView = new TrailMapMediaMarkerView({ model: self.model, map: self.map, latlng: e.latlng, timezoneData: self.timezoneData });
          trailMapMediaMarkerView.render();
          self.arrMapMediaViews.push(trailMapMediaMarkerView);        
        }
      }
    }    
  });

  return TrailMapView;
});
