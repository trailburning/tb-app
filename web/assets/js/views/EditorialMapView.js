define([
  'underscore', 
  'backbone',
  'views/EditorialMapMarkerView'  
], function(_, Backbone, EditorialMapMarkerView){

  var EditorialMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#editorialMapViewTemplate').text());        

      app.dispatcher.on("EditorialMapMarkerView:markerclick", this.onEditorialMapMarkerClick, this);
            
      this.bRendered = false;
      this.arrLineCordinates = [];
      
      var self = this;
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    render: function(){
      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        this.map.fitBounds(this.polyline.getBounds(), {paddingTopLeft: [20, 30], paddingBottomRight: [30, 20]});
        return;         
      }        

      var self = this;
                
      $(this.el).html(this.template());
            
      this.map = L.mapbox.map('map', null, {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.map-kply0zpa');
      this.map.addLayer(this.layer_street);
                          
      var arrMarkers = [];

      _.each(TB_EDITORIAL_TRAILS, function (trail) {
      	  var mapMarker = new EditorialMapMarkerView({map: self.map, trail: trail});
      	  mapMarker.render();
          arrMarkers.push([trail.lat, trail.long]);                   
      }, this);

      var bounds = new L.LatLngBounds(arrMarkers);
      bounds = bounds.pad(0.5);
      this.map.fitBounds(bounds);

      this.bRendered = true;

      return this;
    },
    onEditorialMapMarkerClick: function(editorialMapMarkerView){
      window.location = editorialMapMarkerView.options.trail.url;
	}        
  });

  return EditorialMapView;
});
