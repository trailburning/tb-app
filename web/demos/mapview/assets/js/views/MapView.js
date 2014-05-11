TB_RESTAPI_BASEURL = 'http://localhost:8888/trailburning_api';
//TB_RESTAPI_BASEURL = 'http://www.trailburning.com/api';

define([
  'underscore', 
  'backbone',
  'views/TrailsTrailCardView'  
], function(_, Backbone, TrailsTrailCardView){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var MapView = Backbone.View.extend({
    initialize: function(){
      var self = this;
    	
      app.dispatcher.on("TrailsTrailCardView:markerclick", self.onTrailCardViewMarkerClick, this);
      app.dispatcher.on("TrailsTrailCardView:cardmarkerclick", self.onTrailCardViewCardMarkerClick, this);
      
      this.PageSize = 50;
	  this.nPage = 0;
	  this.currTrailCardView = null;
      this.elCntrls = $('#view_map_btns');
	  this.nMapView = MAP_STREET_VIEW;	        
	  this.collection = new Backbone.Collection();
      
      this.map = L.mapbox.map('map', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.map-omeomj70');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);
                          
	  this.getResults();
	  this.buildBtns();
	  
      $(window).resize(function() {
        self.handleResize();
      });    
	  this.handleResize();
    },
    handleResize: function(){
      var nHeight = 600;
      var nTopMargin = 63;
      
      if (($(window).height() - nTopMargin) > nHeight) {
      	nHeight = $(window).height() - nTopMargin; 
      }
      
      $('#bodyview').height(nHeight);
      $('#map').height(nHeight);
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
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
		  		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?limit='+this.PageSize+'&offset=' + nOffSet;
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
		  self.onTrailCardsResult(data);
        }
      });        
    },
    onTrailCardsResult: function(data){
	  if (!data.value.routes.length) {
	  	return;
	  }

      var self = this;
            
	  this.markerCluster = new L.MarkerClusterGroup({ showCoverageOnHover: false,
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
      
      var model;
      $.each(data.value.routes, function(key, card) {      	    	
	    model = new Backbone.Model(card);	          
	    self.collection.add(model);
	          			  
	    trailsTrailCardView = new TrailsTrailCardView({ model: model, map: self.map, mapCluster: self.markerCluster});
    	$('#trailCards').append(trailsTrailCardView.render().el);      	  				  
      });       
	  this.map.addLayer(this.markerCluster);
	  this.map.fitBounds(this.markerCluster.getBounds());
//	  this.map.setMaxBounds(this.markerCluster.getBounds());
	  	
	  $(this.elCntrls).show();            	        
    },
    onTrailCardViewCardMarkerClick: function(trailCardView){	  
	  this.markerCluster.zoomToShowLayer(trailCardView.marker, function() { console.log('z'); });
      this.onTrailCardViewMarkerClick(trailCardView);      
    },
    onTrailCardViewMarkerClick: function(trailCardView){
      if (this.currTrailCardView) {
      	this.currTrailCardView.selected(false);
      }    	
      trailCardView.selected(true);
    	
      var nY = $(trailCardView.el).position().top;
	  var nYDiff = Math.abs($('#cardsview').scrollTop() - nY);
	  var nDuration = 0;
	   
	  if (nYDiff > 0 && nYDiff < 500) {
	  	nDuration = 1000;
	  }
	  if (nYDiff > 500) {
	  	nDuration = 3000;
	  }

	  $('#cardsview').animate({scrollTop:nY}, nDuration);        
	  $('.panel').removeClass('selected');
	  $('.panel[data-id='+trailCardView.model.cid+']').addClass('selected');
	  
	  this.currTrailCardView = trailCardView;      	        
    }
    
  });

  return MapView;
});
