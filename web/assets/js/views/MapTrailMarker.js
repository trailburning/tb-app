define([
  'underscore', 
  'backbone',
  'models/TrailModel'
], function(_, Backbone, TrailModel){

  var MapTrailMarker = Backbone.View.extend({
    initialize: function(){
      this.trailModel = new TrailModel();    	
      this.bRendered = false;
      this.bTrailRendered = false;
      this.bSelected = false;
      this.polyline = null;
      this.arrLineCordinates = [];
      
      this.inactive_polyline_options = {
        color: '#44B6FC',
        opacity: 0.8,
        weight: 6,
        clickable: true
      };         
	  
      this.active_polyline_options = {
        color: '#ed1c24',
        opacity: 1,
        weight: 6,
        clickable: true
      };         
      
    },            
    showTrail: function(){    
      if (this.polyline) {
        this.polyline.addTo(this.options.map);	
      }
    },
    hideTrail: function(){
      if (this.polyline) {
        this.options.map.removeLayer(this.polyline);
      }    
    },
    render: function(){
      var self = this;

      if (!this.bRendered) {
        // add to map
        function onClick(evt) {
		  // fire event
          app.dispatcher.trigger("MapTrailMarker:click", self);                
	    }
	    function onMouseOver(evt){
	  	  self.onMouseOver(evt);
	    }
	    function onMouseOut(evt){
	  	  self.onMouseOut(evt);
	    }
	    
	    this.marker = L.marker(new L.LatLng(this.model.get('start')[1], this.model.get('start')[0])).on('click', onClick).on('mouseover', onMouseOver).on('mouseout', onMouseOut);			  
	    this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));      	  
		this.options.mapCluster.addLayer(this.marker);      	  
	  }
      this.bRendered = true;
                       
      return this;
    },
	renderTrail: function(){
      if (this.bTrailRendered) {
      	this.showTrail();
      	return;
      }
		
	  var self = this;
		
	  function onClick(evt){
	    // fire event
        app.dispatcher.trigger("MapTrailMarker:click", self);                
	  }
	  function onMouseOver(evt){
	  	self.onMouseOver(evt);
	  }
	  function onMouseOut(evt){
	  	self.onMouseOut(evt);
	  }
		
      // get trail    
      this.trailModel.set('id', this.model.id);             
      this.trailModel.fetch({
        success: function () {        
      	  var data = self.trailModel.get('value');      
      	  $.each(data.route.route_points, function(key, point) {
        	self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      	  });
      	  self.polyline = L.polyline(self.arrLineCordinates, self.inactive_polyline_options).on('click', onClick).on('mouseover', onMouseOver).on('mouseout', onMouseOut);
      	  self.showTrail();
      	  self.bTrailRendered = true;      
        }      
      });            
  	},
	select: function(){
	  // fire event
      app.dispatcher.trigger("MapTrailMarker:click", this);                
	},		
	selected: function(bSelected){	
	  this.bSelected = bSelected;
	  if (bSelected) {
		$(this.marker._icon).addClass('selected');
		if (this.polyline) {
          this.polyline.setStyle(this.active_polyline_options);	  	
	      this.polyline.bringToFront();
	    }
	  }
	  else {
  	  	$(this.marker._icon).removeClass('selected');
  	  	if (this.polyline) {
          this.polyline.setStyle(this.inactive_polyline_options);
        }	  		  	
	  }		
	},
	onMouseOver: function(evt){	
  	  if (!this.bSelected) {
		$(this.marker._icon).addClass('selected');
		if (this.polyline) {
	      this.polyline.setStyle(this.active_polyline_options);
	      this.polyline.bringToFront();
		}
  	  }
    },
	onMouseOut: function(evt){	
  	  if (!this.bSelected) {  	  	
  	  	$(this.marker._icon).removeClass('selected');
        this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));
        if (this.polyline) {
	      this.polyline.setStyle(this.inactive_polyline_options);
	    }
	  }
  	}
  });

  return MapTrailMarker;
});
