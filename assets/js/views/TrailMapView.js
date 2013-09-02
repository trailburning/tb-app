define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
            
      this.polyline = null;
      this.arrLineCordinates = [];      
    },            
    render: function(){
      console.log('TrailMapView:render');
        
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }

      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
//      this.map = L.mapbox.map('map', null, {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false});
      this.map = L.mapbox.map('map', 'mallbeury.map-omeomj70', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false});                
//      this.map = L.mapbox.map('map', 'mallbeury.map-omeomj70', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:true, zoomAnimation:false});                
                
      // remove previous points
      while (this.arrLineCordinates.length > 0) {
        this.arrLineCordinates.pop();
      }
                
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
      this.polyline = L.polyline(this.arrLineCordinates, polyline_options).addTo(this.map);          
      this.map.fitBounds(this.polyline.getBounds(), {padding: [20, 20]});         
                        
      return this;
    },    
    update: function(){
      console.log('TrailMapView:update');
      
      if (this.polyline) {
        this.map.fitBounds(this.polyline.getBounds(), {padding: [20, 20]});         
      }
    }        
  });

  return TrailMapView;
});
