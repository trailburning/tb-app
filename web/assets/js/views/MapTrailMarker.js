define([
  'underscore', 
  'backbone',
  'models/TrailModel'
], function(_, Backbone, TrailModel){

  var MapTrailMarker = Backbone.View.extend({
    initialize: function(){
      this.trailEvents = this.trailEvents || {};    	
    	
  	  this.trailEvents.dispatcher = _.clone(Backbone.Events);
    	
	  var self = this;
	      	
      this.trailEvents.dispatcher.on("DistanceMarkers:click", function(evt){
		// fire event
        app.dispatcher.trigger("MapTrailMarker:click", self);                     	  	
      }, this);
      this.trailEvents.dispatcher.on("DistanceMarkers:mouseover", function(evt){
	    self.onMouseOver(evt);      	  	
      }, this);
      this.trailEvents.dispatcher.on("DistanceMarkers:mouseout", function(evt){
	  	self.onMouseOut(evt);      	  	
      }, this);
    	
      this.trailModel = new TrailModel();    	
      this.bRendered = false;
      this.bTrailRendered = false;
      this.bTrailVisible = false;
      this.bSelected = false;
      this.polyline = null;
      this.hoverPolyline = null;
      this.arrLineCordinates = [];
    },            
    showTrail: function(){    
      if (this.polyline) {
        this.polyline.addTo(this.options.map);
		this.hoverPolyline.addTo(this.options.map);
		if ($(this.marker._icon).hasClass('selected')) {
			this.selected(true);
		}
		this.bTrailVisible = true;
      }
    },
    hideTrail: function(){
      if (this.polyline) {
        this.options.map.removeLayer(this.polyline);
        this.options.map.removeLayer(this.hoverPolyline);
      } 
      this.bTrailVisible = false;   
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
	    this.marker.setIcon(L.divIcon({className: 'tb-map-location-marker', html: '<div class="marker"></div>', iconSize: [18, 25], iconAnchor: [9, 25],}));      	  
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
	      var nDistanceOffsetMetres = 1000;
	      // over 10k reduce markers
	      if (self.trailModel.get('value').route.length > 10000) {
	        nDistanceOffsetMetres = 2000;
		  }
	      
	      self.blur_polyline_options = {
	        color: '#1f1f1f',
	        opacity: 0.5,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id }
	      };         
		  
	      self.focus_polyline_options = {
	        color: '#1f1f1f',
	        opacity: 1,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id }
	      };               
	
	      self.select_polyline_options = {
	        color: '#ed1c24',
	        opacity: 1,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id }
	      };                       	
        	
      	  var data = self.trailModel.get('value');      
      	  $.each(data.route.route_points, function(key, point) {
        	self.arrLineCordinates.push([Number(point.coords[1]), Number(point.coords[0])]);        
      	  });
      	  self.polyline = L.polyline(self.arrLineCordinates, self.blur_polyline_options);
      	  
          var hover_polyline_options = {
        	color: '#000000',
        	opacity: 0,
        	weight: 20,
        	clickable: true,
	    	distanceMarkers: { offset: 1000, lazy: true, events: self.trailEvents }                    
      	  };               	  
      	  self.hoverPolyline = L.polyline(self.arrLineCordinates, hover_polyline_options).on('click', onClick).on('mousemove', function(evt){
	  	    self.onMouseOver(evt);      	  	
      	  }).on('mouseout', function(evt){
            self.onMouseOut(evt);
      	  });
      	  self.showTrail();
        }      
      });            
	  this.bTrailRendered = true;      
  	},
	select: function(){
	  // fire event
      app.dispatcher.trigger("MapTrailMarker:click", this);                
	},		
	focus: function(){	
	  $(this.marker._icon).addClass('selected');	

	  if (this.polyline) {
        this.polyline.setStyle(this.focus_polyline_options);
	  	this.polyline.addDistanceMarkers();
	  	if (this.bTrailVisible) {
          this.polyline.bringToFront();
          this.hoverPolyline.bringToFront();	  		
	  	}
	  }
	},
	blur: function(){	
  	  $(this.marker._icon).removeClass('selected');  	
  	
      if (this.polyline) {
        this.polyline.setStyle(this.blur_polyline_options);
        this.polyline.removeDistanceMarkers();
      }
	},
	selected: function(bSelected){	
	  this.bSelected = bSelected;
	  if (bSelected) {
	  	this.focus();
	  	if (this.polyline) {
          this.polyline.setStyle(this.select_polyline_options);
        }
	  }
	  else {
	  	this.blur();
	  }		
	},
	onMouseOver: function(evt){	
  	  if (!this.bSelected) {
  	  	this.focus();
  	  }
    },
	onMouseOut: function(evt){	
  	  if (!this.bSelected) {  	 
  	  	this.blur(); 	
	  }
  	}
  });

  return MapTrailMarker;
});
