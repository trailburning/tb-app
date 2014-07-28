define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var MapTrailMarker = Backbone.View.extend({
    initialize: function(){
      this.bRendered = false;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
        // add to map
        function onClick(e) {
		  // fire event
          app.dispatcher.trigger("MapTrailMarker:click", self);                
	    }
        
	    this.marker = L.marker(new L.LatLng(this.model.get('start')[1], this.model.get('start')[0])).on('click', onClick);			  
	    this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));      	  
		this.options.mapCluster.addLayer(this.marker);      	  
	  }
      this.bRendered = true;
                       
      return this;
    },
	select: function(){
	  // fire event
      app.dispatcher.trigger("MapTrailMarker:click", this);                
	},		
	selected: function(bSelected){		
	  if (bSelected) {
        this.marker.setIcon(L.divIcon({className: 'tb-map-marker selected', html: '<div class="marker"></div>', iconSize: [20, 20]}));	  	
	  }
	  else {
        this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));	  		  	
	  }		
	}

  });

  return MapTrailMarker;
});
