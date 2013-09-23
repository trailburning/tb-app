define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMiniMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMiniMapViewTemplate').text());        
            
      this.bRendered = false;
      this.polyline = null;
      this.arrLineCordinates = [];
      
      var self = this;      
      $(window).resize(function() {
        self.render();        
      });                
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    render: function(){
      console.log('TrailMiniMapView:render');
        
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }

      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        this.map.fitBounds(this.polyline.getBounds(), {padding: [20, 20]});
        return;         
      }        

      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      $('.btn', $(this.el)).click(function(evt){
        // fire event
        app.dispatcher.trigger("TrailMiniMapView:viewbtnclick", self);                
      });
      
      this.map = L.mapbox.map('minimap', null, {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
                
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
                      
      // show btn                      
      $('.trailview_toggle', $(this.el)).show();

      this.bRendered = true;

      return this;
    }    
  });

  return TrailMiniMapView;
});
