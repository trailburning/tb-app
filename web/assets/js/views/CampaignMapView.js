define([
  'underscore', 
  'backbone',
  'views/MapTrailMarker'  
], function(_, Backbone, MapTrailMarker){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var SHOW_TRAIL_ZOOM = 13;

  var CampaignMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
            
      var self = this;
      
      app.dispatcher.on("MapTrailMarker:click", self.onSelectTrail, this);
      app.dispatcher.on("CampaignTrailCardView:click", self.onTrailCardViewClick, this);
            
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
	  }, this);
        
	  this.markerCluster.on('clustermouseover', function (evt) {
	  	$(evt.layer._icon).addClass('selected');
	  });

	  this.markerCluster.on('clustermouseout', function (evt) {
	  	$(evt.layer._icon).removeClass('selected');
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
    buildBtns: function(){
      var self = this;

      // make btns more touch friendly
      if (Modernizr.touch) {
        $('.btn-tb', $(this.elCntrls)).addClass('touch_btn');
        $('.btn-tb', $(this.elCntrls)).addClass('btn-tb-mega');
      }      

      $('.zoomin_btn', $(this.elCntrls)).click(function(evt){
        if(self.map.getZoom() < self.map.getMaxZoom()) {
          self.map.zoomIn();
          $('.zoomout_btn', $(self.elCntrls)).attr('disabled', false);
          // fire event
          app.dispatcher.trigger("TrailMapView:zoominclick", self);                
        }
        
        if(self.map.getZoom() >= self.map.getMaxZoom()-1) {
          $('.zoomin_btn', $(self.elCntrls)).attr('disabled', true);
        }
      });

      $('.zoomout_btn', $(this.elCntrls)).click(function(evt){
        if(self.map.getZoom() > self.map.getMinZoom()+3) {
          self.map.zoomOut();                  
          $('.zoomin_btn', $(self.elCntrls)).attr('disabled', false);
          // fire event
          app.dispatcher.trigger("TrailMapView:zoomoutclick", self);                
        }
        
        if(self.map.getZoom() <= self.map.getMinZoom()+4) {
          $('.zoomout_btn', $(self.elCntrls)).attr('disabled', true);
        }        
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
        return;         
      }        
                
      var self = this;
                
      $(this.el).html(this.template());
                        
      this.map = L.mapbox.map('map_large', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:true, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.8d4ad8ec');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);

	  this.map.on('move', function() {
	  	self.showTrailsInView();
      });                 
      this.bRendered = true;
                        
      return this;
    },
    showTrailsInView: function(){
      var self = this;
	  var inBounds = [], bounds = this.map.getBounds();

 	  this.collection.each(function(cardModel) { 			
	    if (self.map.getZoom() >= SHOW_TRAIL_ZOOM) {
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
      
      this.nCurrCard = this.collection.indexOf(cardModel);
      
	  // fire event
      app.dispatcher.trigger("TrailMapView:selecttrail", id);                
    },    
    onTrailCardViewClick: function(trailCardView){
	  var latLng = this.map.getCenter(); 
	  // save
	  $.cookie('route_id', $(trailCardView.el).attr('data-id'));
	  $.cookie('route_lat', latLng.lat);
	  $.cookie('route_lng', latLng.lng);
	  $.cookie('route_zoom', this.map.getZoom());
	  	  	  	
	  window.location = $('.link', trailCardView.el).attr('data-url');	  	
	},        
    onSelectTrail: function(trailCardMarker){
      this.selectTrail(trailCardMarker.model.id);    	
    }
    
  });

  return CampaignMapView;
});
