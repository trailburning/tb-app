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

  var WORLD_VIEW = 0;
  var REGION_VIEW = 1;

  var MapView = Backbone.View.extend({
    initialize: function(){
      var self = this;
    	
      app.dispatcher.on("MapTrailMarker:click", self.onTrailMarkerClick, this);
      app.dispatcher.on("MapTrailCardView:click", self.onTrailCardViewClick, this);
      app.dispatcher.on("MapTrailEventCardView:click", self.onTrailCardViewClick, this);
      app.dispatcher.on("MapTrailCardView:cardmarkerclick", self.onTrailCardViewCardMarkerClick, this);
  	  
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
	  var RouteCollection = Backbone.Collection.extend({
    	comparator: function(item) {
    	  // sort by distance
          return item.distance;
    	}
	  });
      this.routeCollection = new RouteCollection();    
      this.nCurrRouteCard = 0;
      
      this.bFlipLock = false;
      this.PageSize = 100;
	  this.nPage = 0;
	  this.nView = WORLD_VIEW;
	  this.nCurrCard = -1;
	  this.currCardModel = null;
      this.elCntrls = $('#view_map_btns');
	  this.nMapView = MAP_STREET_VIEW;	        
	  this.collection = new Backbone.Collection();
      this.map = L.mapbox.map('map', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.idjhlejc');            
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);
	  this.currMarkerOrCluster = null;      
                          
	  this.getResults();
	  this.buildBtns();
	  
	  this.scrollTimer = null;

	  // do we have a route to select?
	  var nRouteID = $.cookie('route_id');
	  if (nRouteID == undefined) {
	  	$('#welcome_view').show();
	  }

	  $('#cardsview').bind('mouseover', function(e){
	  	$('body').addClass('stop_scroll');
	  });

	  $('#cardsview').bind('mouseout', function(e){
	  	$('body').removeClass('stop_scroll');
	  });

	  $('#cardsview').bind('mousewheel', function(evt){
	  	if (evt.originalEvent.wheelDelta > 0) {
	  	  self.prevCard();
	  	}
	  	else {
	  	  self.nextCard();
	  	}
	  });
		  	  
	  $('.nav .prev').click(function(evt){
	  	self.prevCard();
	  });

	  $('.nav .next').click(function(evt){
	  	self.nextCard();
	  });

	  $('.discover_btn').click(function(evt){
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
        self.map.zoomIn();                  
        $('.zoomout_btn', $(self.elCntrls)).attr('disabled', false);        
        self.selectMarkerOrCluster();
        // fire event
        app.dispatcher.trigger("TrailMapView:zoominclick", self);
      });

      $('.zoomout_btn', $(this.elCntrls)).click(function(evt){
        self.map.zoomOut();                  
        $('.zoomin_btn', $(self.elCntrls)).attr('disabled', false);
        self.selectMarkerOrCluster();          
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
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
		  		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?limit='+this.PageSize+'&offset=' + nOffSet;
//	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?order=distance&radius=20000&lat=46.0560029116&long=8.96594457161&limit='+this.PageSize+'&offset=' + nOffSet;
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
    selectCard: function(nId, bMoveForward){    	
      $('#welcome_view').hide();
      $('#cards_container_view').show();
    	
      if (this.currCardModel) {
      	this.currCardModel.mapTrailCardView.hide();      	
      }

      var cardModel = this.collection.get(nId);
      $('#cardsview').append(cardModel.mapTrailCardView.render().el);      
	  cardModel.mapTrailCardView.show();
      
	  this.currCardModel = cardModel;      	          
      
      this.nCurrCard = this.collection.indexOf(cardModel);
    },
    prevCard: function(){
      if (this.bFlipLock) {
      	return;
      }
      this.bFlipLock = true;
    	
      if (this.currCardModel) {
      	this.currCardModel.mapTrailMarker.selected(false);
      }
    	      
      switch (this.nView) {
      	case WORLD_VIEW:
      	  if (this.nCurrCard-1 < 0) {
      		this.nCurrCard = this.collection.length-1;      	
      	  }
      	  else {
        	this.nCurrCard--;
      	  }
	      
	      cardModel = this.collection.at(this.nCurrCard);      
		  this.selectCard(cardModel.id, true);	  
		  this.panMap(cardModel, true);
      	  break;
      	  
      	case REGION_VIEW:
      	  cardModel = this.routePrevPoint(this.currCardModel);
      	  if (cardModel) {
		    this.selectCard(cardModel.id, true);
		    this.panMap(cardModel, false);	  
		  }
      	  break;
      }
      
	  var self = this;
	  if (!this.scrollTimer) {
        this.scrollTimer = setTimeout(function() {
	      clearTimeout(self.scrollTimer);
	      self.scrollTimer = null;
	      self.bFlipLock = false;
	  	}, 1000);	  			  	    
	 }                	
    },    
    nextCard: function(){
      if (this.bFlipLock) {
      	return;
      }
      this.bFlipLock = true;
      var cardModel = null;
      
      if (this.currCardModel) {
      	this.currCardModel.mapTrailMarker.selected(false);
      }
      
      switch (this.nView) {
      	case WORLD_VIEW:
      	  // start on 1st trail
	      cardModel = this.collection.at(0);      
      	  if (cardModel) {
      	  	this.routeInit(cardModel);
      	  	
		    this.selectCard(cardModel.id, true);
		    this.panMap(cardModel, false);
		    
		    this.nView = REGION_VIEW; 	        	  	
      	  }
      	  break;
      	  
      	case REGION_VIEW:
      	  cardModel = this.routeNextPoint(this.currCardModel);
      	  if (cardModel) {
		    this.selectCard(cardModel.id, true);
		    this.panMap(cardModel, false);	        	  	
      	  }
      	  break;
      }
	  	  	  
	  var self = this;
	  if (!this.scrollTimer) {
        this.scrollTimer = setTimeout(function() {
	      clearTimeout(self.scrollTimer);
	      self.scrollTimer = null;
	      self.bFlipLock = false;
	  	}, 1000);	  			  	    
	 }
    },
    routeInit: function(cardModel){
      this.nCurrRouteCard = 0;
      var self = this, nDistance = 0;
      
      this.routeCollection.reset();
      
	  var latLng = cardModel.mapTrailMarker.marker.getLatLng();
      // add all points to route
      this.collection.each(function(model) {      	
        nDistance = model.mapTrailMarker.marker.getLatLng().distanceTo(latLng);      	
      	model.distance = nDistance; 
        self.routeCollection.push(model);
      });                  
	  this.routeCollection.sort();
    },    
    routePrevPoint: function(cardModel){
      if (this.nCurrRouteCard-1 < 0) {
      	this.nCurrRouteCard = this.routeCollection.length - 1;
	  }
	  else {
	    this.nCurrRouteCard--;	  	
	  }    	
	  var nearestCardModel = this.routeCollection.at(this.nCurrRouteCard); 	
    	
      return nearestCardModel;
   	},    
    routeNextPoint: function(cardModel){
      if (this.nCurrRouteCard+1 >= this.routeCollection.length) {
      	this.nCurrRouteCard = 0;
	  }
	  else {
	    this.nCurrRouteCard++;	  	
	  }    	
	  var nearestCardModel = this.routeCollection.at(this.nCurrRouteCard); 	
    	
      return nearestCardModel;
    },
    selectMarkerOrCluster: function(){    
      var self = this;
      
	  // allow time for pan and select again
      setTimeout(function() {
        self.selectCluster();
      }, 500);
	  this.selectCluster();
    },
    panMap: function(cardModel, bZoomWorld){
	  if (bZoomWorld) {
	    this.map.setZoom(3);	
	  }            
	  
	  this.selectMarkerOrCluster();
	  // pan to marker
	  this.map.panTo(cardModel.mapTrailMarker.marker.getLatLng(), {duration: 0.5});        	  	
    },
    selectCluster: function(){
      if (this.nCurrCard == -1) {
      	return;
      }

      var cardModel = this.collection.at(this.nCurrCard);      
    	
	  if (this.currMarkerOrCluster) {
	  	$(this.currMarkerOrCluster._icon).removeClass('selected');
	  }
	  this.currMarkerOrCluster = this.markerCluster.getVisibleParent(cardModel.mapTrailMarker.marker);
	  if (this.currMarkerOrCluster) {
	    $(this.currMarkerOrCluster._icon).addClass('selected');	  	
	  }    	
	},    
    onTrailCardsResult: function(data){
	  if (!data.value.routes.length) {
	  	return;
	  }

      var self = this;

	  this.markerCluster = new L.MarkerClusterGroup({ showCoverageOnHover: false, spiderfyOnMaxZoom: false,
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
      	  // start on 1st trail
	      cardModel = this.collection.get(nRouteID);      
      	  if (cardModel) {
      	  	this.routeInit(cardModel);
      	  	
		    this.selectCard(cardModel.id, true);
	
	  	    // select marker      
        	this.markerCluster.zoomToShowLayer(cardModel.mapTrailMarker.marker, function() {});
        	cardModel.mapTrailMarker.selected(true);
		    
		    this.nView = REGION_VIEW; 	        	  	
      	  }
	  	// remove
	  	$.removeCookie('route_id');
	  }   	        
    },
    onTrailCardViewClick: function(trailCardView){
	  // save
	  $.cookie('route_id', $(trailCardView.el).attr('data-id'));	  	  	
	  window.location = $('.link', trailCardView.el).attr('data-url');	  	
	},    
    onTrailCardViewCardMarkerClick: function(trailCardView){
      this.nView = REGION_VIEW;
    	
	  if (this.currMarkerOrCluster) {
	  	$(this.currMarkerOrCluster._icon).removeClass('selected');
	  	this.currMarkerOrCluster = null;
	  }
    	
      var cardModel = this.collection.get(trailCardView.model.id);
      cardModel.mapTrailMarker.selected(true);
	  
	  this.markerCluster.zoomToShowLayer(cardModel.mapTrailMarker.marker, function() {});
	  this.map.panTo(cardModel.mapTrailMarker.marker.getLatLng(), {animate: false});	
	  
	  this.routeInit(cardModel);
    },      		
    onTrailMarkerClick: function(trailCardMarker){
      this.nView = REGION_VIEW;
    	
	  if (this.currMarkerOrCluster) {
	  	$(this.currMarkerOrCluster._icon).removeClass('selected');
	  	this.currMarkerOrCluster = null;
	  }

      if (this.currCardModel) {
      	this.currCardModel.mapTrailMarker.selected(false);      	
      }

      this.selectCard(trailCardMarker.model.id, true);
    	    	
      var cardModel = this.collection.get(trailCardMarker.model.id);
      
	  // select marker      
      cardModel.mapTrailMarker.selected(true);      
	  this.currCardModel = cardModel;      	          
      
      this.nCurrCard = this.collection.indexOf(cardModel);
      
      this.routeInit(cardModel);
    }
       
  });

  return MapView;
});
