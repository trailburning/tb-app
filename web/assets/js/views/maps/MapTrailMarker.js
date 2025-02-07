define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'views/maps/MapTrailMarkerPopup'
], function(_, Backbone, TrailModel, MapTrailMarkerPopup){

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
		if (this.bSelected) {
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
    showPopup: function(){
      this.popup.show();
    },    
    hidePopup: function(){
      this.popup.hide();
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
	    this.marker.setIcon(L.divIcon({className: 'tb-map-location-marker', html: '<div class="marker"></div>', iconSize: [22, 30], iconAnchor: [11, 30]}));      	  
		this.options.mapCluster.addLayer(this.marker);

	    this.popup = new MapTrailMarkerPopup({ model: this.model, map: this.options.map });
		this.popup.render();
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
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker' }
	      };         
		  
	      self.focus_polyline_options = {
	        color: '#1f1f1f',
	        opacity: 1,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker' }
	      };               
	
	      self.select_polyline_options = {
	        color: '#ed1c24',
	        opacity: 1,
	        weight: 4,
	        clickable: false,
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker' }
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
	        distanceMarkers: { offset: nDistanceOffsetMetres, lazy: true, events: self.trailEvents, id: self.model.id, strClassName: 'dist-marker-active' }
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
	  this.marker.setZIndexOffset(2);

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
	  if (this.bSelected) {
		return;
	  }
				
  	  $(this.marker._icon).removeClass('selected');  	
	  this.marker.setZIndexOffset(0);
  	
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
          this.hoverPolyline.addDistanceMarkers();
          this.polyline.setStyle(this.select_polyline_options);
        }
	  }
	  else {
	  	this.blur();
	  	if (this.polyline) {	
          this.hoverPolyline.removeDistanceMarkers();
        }
	  }		
	},
	onMouseOver: function(evt){	
  	  if (!this.bSelected) {
		// fire event
        app.dispatcher.trigger("MapTrailMarker:focus", this);                     	  	
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
