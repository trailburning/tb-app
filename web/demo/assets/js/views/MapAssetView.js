define([
  'underscore', 
  'backbone',
  'mapbox',
  'views/MarkerView'
], function(_, Backbone, mapbox, MarkerView){
  
  var MapAssetView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      app.dispatcher.on("MarkerView:click", this.onMarkerSelect, this);

      this.bRendered = false;

      this.markerView = null;
      this.map = L.mapbox.map('mapbox-asset-view', 'mallbeury.8d4ad8ec', {dragging: true, touchZoom: false, scrollWheelZoom: false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, markerZoomAnimation:false, attributionControl:false, minZoom: 2, maxZoom: 17});
	  },
    
    render: function(){
      var self = this;

      // already rendered?  Just update
      if (this.bRendered) {
        return;
      }

      this.map.featureLayer.setGeoJSON(this.options.jsonRoute);

      this.map.invalidateSize(false);
      this.map.fitBounds(this.map.featureLayer.getBounds(), {padding: [100, 100], reset: true});

      this.bRendered = true;

      return this;
    },

    focus: function(jsonAsset){
      if (this.markerView) {
        this.markerView.destroy();
      }
      this.markerView = new MarkerView({jsonMedia: jsonAsset, map: this.map, mapLayer: this.map, bLarge: true});
      this.markerView.render();

      this.map.invalidateSize(false);
      this.map.setView([jsonAsset.coords.lat, jsonAsset.coords.long], 14, {animate: false});
    }
	
  });

  return MapAssetView;
});
