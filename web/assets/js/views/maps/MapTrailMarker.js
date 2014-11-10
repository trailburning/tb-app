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
	  this.popup = null;
	      	
      this.trailEvents.dispatcher.on("DistanceMarkers:click", function(evt){
      	self.showPopup();
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
      var popup_options = {
        autoPan: true,
        closeButton: true,
        maxWidth: 500,
        offset: [0, -15],
        autoPanPaddingTopLeft: [300, 200]
      };                
        
      this.popup = L.popup(popup_options)
      .setLatLng([this.marker.getLatLng().lat, this.marker.getLatLng().lng])
      .setContent(this.popupContainer[0])
      .openOn(this.options.map);  
      
  	  // reset      
      $('.tb-trailpopup.fade_on_load').removeClass('tb-fade-in');
      $('.tb-trailpopup.image_container').css('opacity', 0);
      
	  // scale images when loaded
	  var elImages = $('.tb-trailpopup .scale');
	  var imgLoad = imagesLoaded(elImages);
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $('.tb-trailpopup img.scale_image_ready').imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.tb-trailpopup .fade_on_load').addClass('tb-fade-in');
        $('.tb-trailpopup .image_container').css('opacity', 1);
      });
	  // invoke resrc      
      resrc.resrc($('.tb-trailpopup .scale'));                      
    },    
    hidePopup: function(){
      if (this.popup) {
     	this.options.map.closePopup(this.popup);
      }
    },
    render: function(){
      var self = this;

      if (!this.bRendered) {
        // add to map
        function onClick(evt) {
		  self.showPopup();        	
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
	    this.marker.setIcon(L.divIcon({className: 'tb-map-location-marker', html: '<div class="marker"></div>', iconSize: [18, 25], iconAnchor: [9, 25]}));      	  
		this.options.mapCluster.addLayer(this.marker);

        this.popupContainer = $('<div />');      
        this.popupContainer.on('click', '.btnView', function(evt) {
          // fire event          
          app.dispatcher.trigger("MapTrailDetail:click", this);                        
        });
      	this.popupContainer.html('<div class="tb-trailpopup"><div class="image_container fade_on_load"><img data-src="http://app.resrc.it/O=80/http://media.trailburning.com'+this.model.get('media').versions[0].path+'" class="resrc scale"></div><div class="detail_container"><h3 class="tb">'+this.model.get('name')+'</h3><h4 class="tb">'+this.model.get('region')+'</h4><div class="btns"><span data-url="'+TB_PATH+'/trail/'+this.model.get('slug')+'" data-id="'+this.model.id+'" class="btn btn-tb btnView">View Trail</span></div></div></div>');      	
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
	  	self.showPopup();
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
