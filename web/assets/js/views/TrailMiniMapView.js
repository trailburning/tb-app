define([
  'underscore', 
  'backbone',
  'views/TrailMapMediaMarkerView'  
], function(_, Backbone, TrailMapMediaMarkerView){

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
              iconSize:     [27, 35],
              iconAnchor:   [12, 33],
              popupAnchor:  [12, 33]
          }
      });      
      this.locationIcon = new LocationIcon({iconUrl: 'http://assets.trailburning.com/images/icons/sm_location.png'});
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
      
      if (this.arrMapMediaViews.length) {
        this.currMapMediaView = this.arrMapMediaViews[nMedia];
        this.currMapMediaView.setActive(true);
      }      
    },
    addMedia: function(mediaModel){
      var trailMapMediaMarkerView = new TrailMapMediaMarkerView({ size: SMALL_ICONS, map: this.map, model: mediaModel });
      this.arrMapMediaViews.push(trailMapMediaMarkerView);
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
            
      this.map = L.mapbox.map('minimap', null, {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
//      this.layer_street = L.mapbox.tileLayer('mallbeury.map-kply0zpa');
//      this.layer_street = L.mapbox.tileLayer('mallbeury.jddb98b0');
      this.layer_street = L.mapbox.tileLayer('mallbeury.8f5ac718');
      
      this.map.addLayer(this.layer_street);
                
      // remove previous points
      while (this.arrLineCordinates.length > 0) {
        this.arrLineCordinates.pop();
      }
                
      var data = this.model.get('value');      
      $.each(data.route.route_points, function(key, point) {
        self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      });

      var polyline_options = {
        color: '#FFF',
        opacity: 1,
        weight: 4,
        clickable: false
      };         
      this.polyline = L.polyline(this.arrLineCordinates, polyline_options).addTo(this.map);          
      this.map.fitBounds(this.polyline.getBounds(), {paddingTopLeft: [20, 30], paddingBottomRight: [30, 20]});         

      this.renderMarkers();
                      
      $(window).resize(function() {
        self.render();        
      });                
                      
      this.bRendered = true;

      return this;
    }    
  });

  return TrailMiniMapView;
});
