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
      this.hoverPolyline = null;
      this.arrLineCordinates = [];
      
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 58],
              popupAnchor:  [16, 44]
          }
      });      
      this.locationIcon = new LocationIcon({iconUrl: 'http://assets.trailburning.com/images/icons/location.png'});      
      
      this.inactive_polyline_options = {
        color: '#44B6FC',
        opacity: 0.6,
        weight: 6,
        clickable: false,
        distanceMarkers: { offset: 1000, lazy: true }
      };         
	  
      this.active_polyline_options = {
        color: '#ed1c24',
        opacity: 0.8,
        weight: 6,
        clickable: false
      };               
    },            
    showTrail: function(){    
      if (this.polyline) {
        this.polyline.addTo(this.options.map);
        this.hoverPolyline.addTo(this.options.map);	
      }
    },
    hideTrail: function(){
      if (this.polyline) {
        this.options.map.removeLayer(this.polyline);
        this.options.map.removeLayer(this.hoverPolyline);
      }    
    },
    render: function(){
      var self = this;

      if (!this.bRendered) {
        // add to map
        function onClickLocation(evt) {
        }
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
	    
        this.locationMarker = L.marker([this.model.get('start')[1], this.model.get('start')[0]], {icon: this.locationIcon, zIndexOffset: 1000}).on('click', onClickLocation);	    
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
//      	  self.polyline = L.polyline(self.arrLineCordinates, self.inactive_polyline_options).on('click', onClick).on('mouseover', onMouseOver).on('mouseout', onMouseOut);
      	  self.polyline = L.polyline(self.arrLineCordinates, self.inactive_polyline_options);
      	  
          var hover_polyline_options = {
        	color: '#ff0000',
        	opacity: 0,
        	weight: 20,
        	clickable: true,
	    	distanceMarkers: { offset: 1000, lazy: true }                    
      	  };               	  
      	  self.hoverPolyline = L.polyline(self.arrLineCordinates, hover_polyline_options).on('click', onClick).on('mouseover', onMouseOver).on('mouseout', onMouseOut);
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
	
	  this.locationMarker.addTo(this.options.map);
	
	  if (this.polyline) {
        this.polyline.setStyle(this.active_polyline_options);
	    this.polyline.addDistanceMarkers();
        this.polyline.bringToFront();
        this.hoverPolyline.bringToFront();        
	  }
	},
	blur: function(){	
  	  $(this.marker._icon).removeClass('selected');
  	
  	  this.options.map.removeLayer(this.locationMarker);
  	
      this.marker.setIcon(L.divIcon({className: 'tb-map-marker', html: '<div class="marker"></div>', iconSize: [20, 20]}));
      if (this.polyline) {
        this.polyline.setStyle(this.inactive_polyline_options);
        this.polyline.removeDistanceMarkers();
      }
	},
	selected: function(bSelected){	
	  this.bSelected = bSelected;
	  if (bSelected) {
	  	this.focus();
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
