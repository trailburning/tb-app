define([
  'underscore', 
  'backbone',
  'views/MapTrailMarker'  
], function(_, Backbone, MapTrailMarker){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var CampaignMapView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMapViewTemplate').text());        
            
      var self = this;
      
      app.dispatcher.on("MapTrailMarker:click", self.onTrailMarkerClick, this);
            
      this.elCntrls = this.options.elCntrls;            
      this.bRendered = false;
      this.map = null;
      this.nMapView = MAP_STREET_VIEW;
      this.nCurrCard = -1;
      this.collection = new Backbone.Collection();
      
	  this.getResults();
	  this.buildBtns();
    },            
    show: function(){
      $(this.el).fadeIn(500, 'linear');
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
      
      $('#view_map_btns .view_btn', $(this.elCntrls)).click(function(evt){
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
      var latlng = new L.LatLng(51.507351, -0.127758);
    	
      // already rendered?  Just update
      if (this.bRendered) {
        this.map.invalidateSize();
        this.map.setView(latlng, 12);	
        return;         
      }        
                
      var self = this;
                
      $(this.el).html(this.template());
                        
      this.map = L.mapbox.map('map_large', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:true, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.8d4ad8ec');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);

      this.map.setView(latlng, 12);	

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
	    if (self.map.getZoom() <= 12) {
	      cardModel.mapTrailMarker.hideTrail();
 	  	}
 	  	else {
	      if (bounds.contains(cardModel.mapTrailMarker.marker.getLatLng())) {
		    cardModel.mapTrailMarker.renderTrail();
	      }
 	  	}
	  });	    	    
	},    
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
		  		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?order=distance&radius=200&lat=51.507351&long=-0.127758&limit=500&offset=0';
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
		  self.onCampaignCardsResult(data);
        }
      });        
    },
    selectMarkerOrCluster: function(){
      if (this.nCurrCard == -1) {
      	return;
      }
    	    
      var self = this;
      
      var cardModel = this.collection.at(this.nCurrCard);      
    	
	  if (this.currMarkerOrCluster) {
	  	$(this.currMarkerOrCluster._icon).removeClass('selected');
	  }
	  this.currMarkerOrCluster = this.markerCluster.getVisibleParent(cardModel.mapTrailMarker.marker);
	  if (this.currMarkerOrCluster) {
	    $(this.currMarkerOrCluster._icon).addClass('selected');	  	
	  }    	

    },    
    onCampaignCardsResult: function(data){
	  if (!data.value.routes.length) {
	  	return;
	  }

      var self = this;

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
        
	  this.markerCluster.on('clusterclick', function (a) {
    	self.selectMarkerOrCluster();
	  });
	          
      var model, cardViewModel;
      $.each(data.value.routes, function(key, card) {
      	bEvent = false;
	    model = new Backbone.Model(card);	    
	    
        var mapTrailMarker = new MapTrailMarker({ model: model, map: self.map, mapCluster: self.markerCluster });        
        mapTrailMarker.render();        

    	cardViewModel = new Backbone.Model();
    	cardViewModel.id = model.id;
    	cardViewModel.mapTrailMarker = mapTrailMarker;
    	self.collection.add(cardViewModel);   	  	
      });       
	  this.map.addLayer(this.markerCluster);
	  this.map.fitBounds(this.markerCluster.getBounds());
	  	
	  $(this.elCntrls).show();         
    },
    onTrailMarkerClick: function(trailCardMarker){
	  if (this.currMarkerOrCluster) {
	  	$(this.currMarkerOrCluster._icon).removeClass('selected');
	  	this.currMarkerOrCluster = null;
	  }

      if (this.currCardModel) {
      	this.currCardModel.mapTrailMarker.selected(false);      	
      }

      var cardModel = this.collection.get(trailCardMarker.model.id);
      
	  // select marker      
      cardModel.mapTrailMarker.selected(true);      
	  this.currCardModel = cardModel;      	          
      
      this.nCurrCard = this.collection.indexOf(cardModel);
    }
    
  });

  return CampaignMapView;
});
