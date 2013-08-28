define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailDisplayView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailDisplayViewTemplate').text());        
            
      this.polyline = null;
      this.arrLineCordinates = [];      
    },            
    render: function(){
      console.log('TrailDisplayView:render');
        
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }

      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      this.map = L.mapbox.map('map', null, {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false});          
      // remove previous points
      while (this.arrLineCordinates.length > 0) {
        this.arrLineCordinates.pop();
      }
                
      var data = this.model.get('value');      
      $.each(data.route.route_points, function(key, point) {
        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      });

      var polyline_options = {
        color: '#000',
        opacity: 0.9,
        weight: 2
      };          
      this.polyline = L.polyline(this.arrLineCordinates, polyline_options).addTo(this.map);          
      this.map.fitBounds(this.polyline.getBounds(), {padding: [20, 20]});         
            
      return this;
    },    
    test: function(){
      if (this.polyline) {
        this.map.fitBounds(this.polyline.getBounds(), {padding: [20, 20]});         
      }
    },    
    renderMedia: function(mediaModel){
      console.log('TrailDisplayView:renderMedia');
      
      var self = this;
      
      var CustomIcon = L.Icon.extend({
          options: {
              iconSize:     [28, 28],
              iconAnchor:   [14, 14],
              popupAnchor:  [0, 0]
          }
      });      
      var mediaIcon = new CustomIcon({iconUrl: 'http://www.trailburning.com/assets/images/icons/marker_media_inactive.png'});
      
      var data = mediaModel.get('value');      
      $.each(data, function(key, point) {
        L.marker([point.coords.lat, point.coords.long], {icon: mediaIcon}).addTo(self.map);      
      });
    }    
  });

  return TrailDisplayView;
});
