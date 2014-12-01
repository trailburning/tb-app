define([
  'underscore', 
  'backbone',
  'views/maps/MapTrailMarker'  
], function(_, Backbone, MapTrailMarker){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var MapTrailView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
            
      var self = this;
      
      app.dispatcher.on("MapTrailMarker:click", self.onSelectTrail, this);
      app.dispatcher.on("TrailCardView:click", self.onTrailCardViewClick, this);
      app.dispatcher.on("MapTrailDetail:click", self.onMapTrailDetailClick, this);
            
      this.elCntrls = this.options.elCntrls;            
      this.bRendered = false;
      this.map = null;
      this.nMapView = MAP_STREET_VIEW;
      this.nCurrCard = -1;
      this.currTrailCardMarker = null;
      this.collection = new Backbone.Collection();

	  this.markerCluster = new L.MarkerClusterGroup({ showCoverageOnHover: false, spiderfyOnMaxZoom: false, disableClusteringAtZoom: 13,
    	iconCreateFunction: function(cluster) {
    	  var nSize = 40;
    	  var strClass = 'tb-map-marker small';
    	  if (cluster._childCount > 9) {
    	  	nSize = 50;
    	  	strClass = 'tb-map-marker medium';
    	  }     	  
    	  if (cluster._childCount > 99) {
    	  	nSize = 60;
    	  	strClass = 'tb-map-marker large';
    	  } 
          return new L.DivIcon({ className: strClass, html: '<div class="marker">' + cluster.getChildCount() + '</div>', iconSize: [nSize, nSize] });
    	}
	  });
	  
	  this.markerCluster.on('animationend', function(evt){
	  	if (self.currTrailCardMarker) {
	      self.currMarkerOrCluster = self.markerCluster.getVisibleParent(self.currTrailCardMarker.marker);
	      if (self.currMarkerOrCluster) {
	        $(self.currMarkerOrCluster._icon).addClass('selected');
	        
    	  	self.showTrailsInView();
	      }	  		
	  	}	  	
	  	self.updateZoomCtrls();
	  }, this);
        
	  this.markerCluster.on('clustermouseover', function (evt) {
	  	$(evt.layer._icon).addClass('focus');
	  });

	  this.markerCluster.on('clustermouseout', function (evt) {
	  	$(evt.layer._icon).removeClass('focus');
	  });

	  this.buildBtns();
    },            
    show: function(){
      $(this.el).fadeIn(500, 'linear');
	  $(this.elCntrls).show();               
    },
    hide: function(){
      $(this.el).fadeOut(500, 'linear');
    },
    updateZoomCtrls: function(){
      if(this.map.getZoom() > this.map.getMinZoom()) {
        $('.zoomout_btn', $(this.elCntrls)).attr('disabled', false);
	  }
	  else {
        $('.zoomout_btn', $(this.elCntrls)).attr('disabled', true);	  	
	  }    	
		
      if(this.map.getZoom() < this.map.getMaxZoom()) {
        $('.zoomin_btn', $(this.elCntrls)).attr('disabled', false);
      }
      else {
        $('.zoomin_btn', $(this.elCntrls)).attr('disabled', true);
      }
    },
    buildBtns: function(){
      var self = this;

      // make btns more touch friendly
      if (Modernizr.touch) {
        $('.btn-tb', $(this.elCntrls)).addClass('touch_btn');
        $('.btn-tb', $(this.elCntrls)).addClass('btn-tb-mega');
      }      

      $('.zoomin_btn', $(this.elCntrls)).click(function(evt){      	
        self.map.zoomIn();      
        // fire event
        app.dispatcher.trigger("TrailMapView:zoominclick", self);                
      });

      $('.zoomout_btn', $(this.elCntrls)).click(function(evt){
        self.map.zoomOut();
        // fire event
        app.dispatcher.trigger("TrailMapView:zoomoutclick", self);                
      });
      
      $('.view_btn', $(this.elCntrls)).click(function(evt){
        switch (self.nMapView) {
          case MAP_SAT_VIEW:
            self.nMapView = MAP_STREET_VIEW;
            
            self.map.removeLayer(self.layer_sat);        
            self.map.addLayer(self.layer_street);                
            self.layer_street.redraw();
            
            $(this).text('Satellite');
            break;
            
          case MAP_STREET_VIEW:
            self.nMapView = MAP_SAT_VIEW;
          
            self.map.removeLayer(self.layer_street);        
            self.map.addLayer(self.layer_sat);  
            self.layer_sat.redraw();

            $(this).text('Map');
            break;          
        }
      });
    },
    render: function(){
      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize(false);
	    this.map.fitBounds(this.markerCluster.getBounds(), {padding: [200, 200], animate: false});
	    this.updateZoomCtrls();
        return;         
      }        
                
      var self = this;
                
      $(this.el).html(this.template());
                        
      this.map = L.mapbox.map('map_large', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:true, attributionControl:false, minZoom: 2, maxZoom: 17});
      this.layer_street = L.mapbox.tileLayer('mallbeury.8d4ad8ec');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);

	  this.map.on('move', function() {
	  	self.showTrailsInView();
      });             
	  this.updateZoomCtrls();          
      this.bRendered = true;
                        
      return this;
    },
    showTrailsInView: function(){
      var self = this;
	  var inBounds = [], bounds = this.map.getBounds();

 	  this.collection.each(function(cardModel) {
 	  	var fLength = cardModel.mapTrailMarker.model.get('length');
 	  	// adjust zoom based on trail length
 	  	var nZoom = 13; 
 	  	if (fLength > 5000) {
 	  	  nZoom = 12;
 	  	}
 	  	if (fLength > 10000) {
 	  	  nZoom = 11;
 	  	}
 	  	if (fLength > 50000) {
 	  	  nZoom = 9;
 	  	}
 	  	 			
	    if (self.map.getZoom() >= nZoom) {
	      if (bounds.contains(cardModel.mapTrailMarker.marker.getLatLng())) {
		    cardModel.mapTrailMarker.renderTrail();
	      }
 	  	}
 	  	else {
	      cardModel.mapTrailMarker.hideTrail();
 	  	}
	  });	    	    
	},    
    addTrail: function(model){
	  var bEvent = false;

      var mapTrailMarker = new MapTrailMarker({ model: model, map: this.map, mapCluster: this.markerCluster });        
      mapTrailMarker.render();        

      var cardViewModel = new Backbone.Model();
      cardViewModel.id = model.id;
      
      cardViewModel.mapTrailMarker = mapTrailMarker;
      this.collection.add(cardViewModel);   	  	    
    },
    updateTrails: function(){
	  this.map.addLayer(this.markerCluster);
	  this.map.fitBounds(this.markerCluster.getBounds(), {padding: [200, 200]});
    },
    setMapView: function(latLng, nZoom){
   	  this.map.setView(latLng, nZoom, {animate: false});
    },
    selectTrail: function(id){
	  if (this.currMarkerOrCluster) {
	  	$(this.currMarkerOrCluster._icon).removeClass('selected');
	  	this.currMarkerOrCluster = null;
	  }
      
      if (this.currTrailCardMarker) {
      	this.currTrailCardMarker.selected(false);
      }
      
      var trailCardMarker = this.collection.get(id).mapTrailMarker;
      this.currTrailCardMarker = trailCardMarker;

	  var cardModel = this.collection.get(id);
      trailCardMarker.selected(true);      
      trailCardMarker.showPopup();      

      this.currMarkerOrCluster = this.markerCluster.getVisibleParent(this.currTrailCardMarker.marker);
      if (this.currMarkerOrCluster) {
        $(this.currMarkerOrCluster._icon).addClass('selected');
	  }      	      
      
      this.nCurrCard = this.collection.indexOf(cardModel);
	  // fire event
      app.dispatcher.trigger("TrailMapView:selecttrail", id);                
    },    
    unselectTrail: function(){
      if (this.currTrailCardMarker) {
        this.currTrailCardMarker.selected(false);      
        this.currTrailCardMarker.hidePopup();            	
        this.currTrailCardMarker = null;	
      }
	},    
    viewTrail: function(id, strURL){
	  var latLng = this.map.getCenter(); 
	  // save
	  $.cookie('route_id', id);
	  $.cookie('route_lat', latLng.lat);
	  $.cookie('route_lng', latLng.lng);
	  $.cookie('route_zoom', this.map.getZoom());
	  	  	  	
	  window.location = strURL;	  	
	},        
    onTrailCardViewClick: function(trailCardView){
	  this.viewTrail($(trailCardView.el).attr('data-id'), $('.link', trailCardView.el).attr('data-url'));    	
	},        
    onMapTrailDetailClick: function(el){
	  this.viewTrail($(el).attr('data-id'), $(el).attr('data-url'));    	
	},        	
    onSelectTrail: function(trailCardMarker){
      this.selectTrail(trailCardMarker.model.id);    	
    }
    
  });

  return MapTrailView;
});
