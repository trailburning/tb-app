  
  var DistanceMarkerView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;
	  },
    render: function(){
      var self = this;

      this.marker = L.marker([this.options.model.get('lat'), this.options.model.get('lng')]);        
      this.marker.setIcon(L.divIcon({className: 'tb-map-distance-marker', html: '<div class="counter">'+this.options.model.get('distance')+'</div>', iconSize: [20, 20], iconAnchor: [10, 10]}));
      this.options.map.addLayer(this.marker);

      return this;
    }
	
  });
