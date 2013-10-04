define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaView'  
], function(_, Backbone, TrailMapMediaView){

  var TrailMiniMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMiniMapViewTemplate').text());        
            
      this.bRendered = false;
      this.polyline = null;
      this.arrLineCordinates = [];
      this.arrMapMediaViews = [];
      this.currMapMediaView = null;
      
      var self = this;
      
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      this.locationIcon = new LocationIcon({iconUrl: 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/location.png'});
            
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
    gotoMedia: function(nMedia){
      // restore previous
      if (this.currMapMediaView) {
        this.currMapMediaView.setActive(false);
      }
      this.currMapMediaView = this.arrMapMediaViews[nMedia];
      this.currMapMediaView.setActive(true);
    },
    addMedia: function(mediaModel){
      var trailMapMediaView = new TrailMapMediaView({ map: this.map, model: mediaModel });
      this.arrMapMediaViews.push(trailMapMediaView);
    },
    renderMarkers: function(){
      if (!this.arrMapMediaViews.length) {
        return;
      }
      
      var trailMapMediaView = null;
      for (var nMedia=0; nMedia < this.arrMapMediaViews.length; nMedia++) {
        trailMapMediaView = this.arrMapMediaViews[nMedia];
        trailMapMediaView.render();
      }
      
      L.marker(this.arrLineCordinates[0], {icon: this.locationIcon}).addTo(this.map);            
    },        
    render: function(){
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }

      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        this.map.fitBounds(this.polyline.getBounds(), {paddingTopLeft: [20, 30], paddingBottomRight: [30, 20]});
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
      this.map.fitBounds(this.polyline.getBounds(), {paddingTopLeft: [20, 30], paddingBottomRight: [30, 20]});         

      this.renderMarkers();
                      
      // show btn                      
      $('.trailview_toggle', $(this.el)).show();

      this.bRendered = true;

      return this;
    }    
  });

  return TrailMiniMapView;
});