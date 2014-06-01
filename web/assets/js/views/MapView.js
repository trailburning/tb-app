define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/MapTrailCardView',
  'views/MapTrailEventCardView',
  'views/MapTrailMarker'
], function(_, Backbone, ActivityFeedView, MapTrailCardView, MapTrailEventCardView, MapTrailMarker){

  var MAP_STREET_VIEW = 0;
  var MAP_SAT_VIEW = 1;

  var MapView = Backbone.View.extend({
    initialize: function(){
      var self = this;
    	
      app.dispatcher.on("MapTrailMarker:click", self.onTrailMarkerClick, this);
      app.dispatcher.on("MapTrailCardView:click", self.onTrailCardViewClick, this);
      app.dispatcher.on("MapTrailEventCardView:click", self.onTrailCardViewClick, this);
  	  
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
      this.bFlipLock = false;
      this.PageSize = 50;
	  this.nPage = 0;
	  this.nCurrCard = -1;
	  this.currCardModel = null;
      this.elCntrls = $('#view_map_btns');
	  this.nMapView = MAP_STREET_VIEW;	        
	  this.collection = new Backbone.Collection();
      
      this.map = L.mapbox.map('map', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.map-omeomj70');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);
                          
	  this.getResults();
	  this.buildBtns();
	  
	  $('.nav .prev').click(function(evt){
	  	self.prevCard();
	  });

	  $('.nav .next').click(function(evt){
	  	self.nextCard();
	  });
	  
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
    prevCard: function(){
      if (this.bFlipLock) {
      	return;
      }
    	
      if (this.currCardModel) {
      	this.currCardModel.mapTrailMarker.selected(false);
      }
    	
      if (this.nCurrCard-1 < 0) {
      	this.nCurrCard = this.collection.length-1;      	
      }
      else {
        this.nCurrCard--;
      }
      var cardModel = this.collection.at(this.nCurrCard);      
      $('#cardsview').html(cardModel.mapTrailCardView.render().el);
                	
	  // select marker      
      this.markerCluster.zoomToShowLayer(cardModel.mapTrailMarker.marker, function() {});
      cardModel.mapTrailMarker.selected(true);
      
	  this.currCardModel = cardModel;      	          
    },    
    nextCard: function(){
      if (this.bFlipLock) {
      	return;
      }
      
      if (this.currCardModel) {
      	this.currCardModel.mapTrailMarker.selected(false);
      }
      
      if (this.nCurrCard+1 >= this.collection.length) {
      	this.nCurrCard = 0;      	
      }
      else {
        this.nCurrCard++;
      }
      var cardModel = this.collection.at(this.nCurrCard);
      $('#cardsview').html(cardModel.mapTrailCardView.render().el);
    	
	  // select marker      
      this.markerCluster.zoomToShowLayer(cardModel.mapTrailMarker.marker, function() {});
      cardModel.mapTrailMarker.selected(true);
      
	  this.currCardModel = cardModel;      	          
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
          mapTrailCardView = new MapTrailEventCardView({ model: model });
        }
        else {
          mapTrailCardView = new MapTrailCardView({ model: model });
        }	          

        var mapTrailMarker = new MapTrailMarker({ model: model, map: self.map, mapCluster: self.markerCluster });
        mapTrailMarker.render();

    	cardViewModel = new Backbone.Model();
    	cardViewModel.id = model.id;
    	cardViewModel.mapTrailCardView = mapTrailCardView;
    	cardViewModel.mapTrailMarker = mapTrailMarker; 
    	self.collection.add(cardViewModel);   	  				  
      });       
	  this.map.addLayer(this.markerCluster);
	  this.map.fitBounds(this.markerCluster.getBounds());
//	  this.map.setMaxBounds(this.markerCluster.getBounds());
	  	
	  $(this.elCntrls).show();         
	  // do we have a route to select?
	  var nRouteID = $.cookie('route_id');
	  if (nRouteID != undefined) {
        var cardModel = this.collection.get(nRouteID);
        $('#cardsview').html(cardModel.mapTrailCardView.render().el);    	
      
	    // select marker      
        this.markerCluster.zoomToShowLayer(cardModel.mapTrailMarker.marker, function() {});
        cardModel.mapTrailMarker.selected(true);
      
	    this.currCardModel = cardModel;      	          
      
        this.nCurrCard = this.collection.indexOf(cardModel);

	  	// remove
	  	$.removeCookie('route_id');
	  }   	        
    },
    onTrailCardViewClick: function(trailCardView){
	  // save
	  $.cookie('route_id', $(trailCardView.el).attr('data-id'));	  	  	
	  window.location = $('.link', trailCardView.el).attr('data-url');	  	
	},    
    onTrailMarkerClick: function(trailCardMarker){
      if (this.currCardModel) {
      	this.currCardModel.mapTrailMarker.selected(false);      	
      	this.currCardModel.mapTrailCardView.hide();      	
      }
    	    	
      var cardModel = this.collection.get(trailCardMarker.model.id);
      $('#cardsview').append(cardModel.mapTrailCardView.render().el);
      
	  // select marker      
      this.markerCluster.zoomToShowLayer(cardModel.mapTrailMarker.marker, function() {});
      cardModel.mapTrailMarker.selected(true);
	  cardModel.mapTrailCardView.show();
      
	  this.currCardModel = cardModel;      	          
      
      this.nCurrCard = this.collection.indexOf(cardModel);
    }
       
  });

  return MapView;
});
