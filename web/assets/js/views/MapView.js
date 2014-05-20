define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/MapTrailCardView',
  'views/MapTrailEventCardView'    
], function(_, Backbone, ActivityFeedView, MapTrailCardView, MapTrailEventCardView){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var MapView = Backbone.View.extend({
    initialize: function(){
      var self = this;
    	
      app.dispatcher.on("MapTrailCardView:markerclick", self.onTrailCardViewMarkerClick, this);
      app.dispatcher.on("MapTrailCardView:cardmarkerclick", self.onTrailCardViewCardMarkerClick, this);
  	  
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
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
      
      var model, cardViewModel, bEvent;
      $.each(data.value.routes, function(key, card) {      	    	
      	bEvent = false;
	    model = new Backbone.Model(card);	    
	    
	    // mla - switch based on route
	    switch (model.get('slug')) {
	      case '16km':
	        case '30km':
	        case '46km':
	          bEvent = true;	          	
	          model.set('eventURL', 'ultraks');
	          break;	          	  
	        case 'e16':
	        case 'e51':
	        case 'e101':
	          bEvent = true;	          	
	          model.set('eventURL', 'eiger');
	          break;	          	  
	        case 'ttm':
	          bEvent = true;	          	
	          model.set('eventURL', 'tfor');
	          break;	          	  
	        case 'marathon':
	          bEvent = true;	          	
	          model.set('eventURL', 'aom');
	          break;	          	  
	        case 'ultramarathon':
	          bEvent = true;	          	
	          model.set('eventURL', 'laugavegur');
	          break;	          	  
	    }
        if (bEvent) {
          mapTrailCardView = new MapTrailEventCardView({ model: model, map: self.map, mapCluster: self.markerCluster });
        }
        else {
          mapTrailCardView = new MapTrailCardView({ model: model, map: self.map, mapCluster: self.markerCluster });
        }	          
    	$('#trailCards').append(mapTrailCardView.render().el);   
    	
    	cardViewModel = new Backbone.Model();
    	cardViewModel.id = model.id;
    	cardViewModel.mapTrailCardView = mapTrailCardView; 
    	self.collection.add(cardViewModel);   	  				  
      });       
	  this.map.addLayer(this.markerCluster);
	  this.map.fitBounds(this.markerCluster.getBounds());
//	  this.map.setMaxBounds(this.markerCluster.getBounds());
	  	
	  $(this.elCntrls).show();         
	  
	  $('#cardsview .trail_card_panel .link').click(function(evt){
	  	window.location = $(this).attr('data-url');	  	
	    // save
	    $.cookie('route_id', $(this).attr('data-id'));	  	  	
	  });	  
	  
	  // do we have a route to select?
	  var nRouteID = $.cookie('route_id');
	  if (nRouteID != undefined) {
	  	var model = this.collection.get(nRouteID);
	  	this.trailCardSelectMarker(model.mapTrailCardView);
	  	// remove
	  	$.removeCookie('route_id');
	  }   	        
    },
    trailCardSelectMarker: function(trailCardView){	  
	  this.markerCluster.zoomToShowLayer(trailCardView.marker, function() {});
	  this.trailCardViewMarkerClick(trailCardView, false);
	},    
    trailCardViewMarkerClick: function(trailCardView, bScroll){	  
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

	  if (bScroll) {
	    $('#cardsview').animate({scrollTop:nY}, nDuration);        	  	
	  }
	  else {
	  	$('#cardsview').scrollTop(nY);
	  }
	  $('.panel').removeClass('selected');
	  $('.panel[data-id='+trailCardView.model.cid+']').addClass('selected');
	  
	  this.currTrailCardView = trailCardView;      	    
	},    
    onTrailCardViewCardMarkerClick: function(trailCardView){	  
	  this.markerCluster.zoomToShowLayer(trailCardView.marker, function() {});
      this.onTrailCardViewMarkerClick(trailCardView);      
    },
    onTrailCardViewMarkerClick: function(trailCardView){
      this.trailCardViewMarkerClick(trailCardView, true);    	
    }    
       
  });

  return MapView;
});
