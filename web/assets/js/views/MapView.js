define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',  
  'views/CampaignMapView',
  'views/CampaignTrailCardView'  
], function(_, Backbone, ActivityFeedView, CampaignMapView, CampaignTrailCardView){
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;
  
  var MapView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.bLocked = true;
      this.collection = new Backbone.Collection();
      this.slideTimer = null;
      this.nCurrSlide = -1;
	  this.bPlayerReady = false;

      this.nPlayerHeight = 0;
      this.nPlayerMinHeight = $('#campaignplayer').height();

      app.dispatcher.on("TrailMapView:selecttrail", self.onSelectTrail, this);
      app.dispatcher.on("TrailMapView:zoominclick", self.onTrailMapViewZoomInClick, this);
      app.dispatcher.on("TrailMapView:zoomoutclick", self.onTrailMapViewZoomOutClick, this);
      app.dispatcher.on("TrailSlidesView:slideview", self.onTrailSlidesViewSlideView, this);

	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }

	  // do we have a route to select?
	  var nRouteID = $.cookie('route_id');
	  if (nRouteID == undefined) {
	  	$('#welcome_view').show();
	  }

      this.trailMapView = new CampaignMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
      this.trailCardView = new CampaignTrailCardView({ el: '#trailcard_view' });
  	  this.trailMapView.render();

	  this.getResults();
	  this.buildBtns();
	  
      $(window).resize(function() {
        self.handleResize();
      });    
	  this.handleResize();
    },
    handleResize: function(){
      var nHeight = 600;
      var nTopMargin = 45;
      
      if (($(window).height() - nTopMargin) > nHeight) {
      	nHeight = $(window).height() - nTopMargin; 
      }
      
      $('#bodyview').height(nHeight);
      $('#trail_map_view').height(nHeight);
   	  // force height update for MapBox
   	  $('#trail_map_view .map_container').height(nHeight);      	
   	  
      if (this.bPlayerReady) {
        this.trailMapView.render();
      }
    },    
    buildBtns: function(){
      var self = this;
      
	  $('.discover_btn').click(function(evt){
     	// get latest trail
	    var cardModel = self.collection.at(0);      
	    // setup map view and select trail
	    self.trailMapView.setMapView(new L.LatLng(cardModel.get('start')[1], cardModel.get('start')[0]), 13);
        self.trailMapView.selectTrail(cardModel.id);		
	  });      
	},    
    render: function(){
  	  this.trailMapView.render();        
	  this.trailCardView.render();
	},
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
		  		  
//	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?order=distance&radius=30&lat=51.507351&long=-0.127758&limit=500&offset=0';
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?limit=500&offset=0';
	  	  
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
          var model;
      	  $.each(data.value.routes, function(key, card) {
	    	model = new Backbone.Model(card);
	    	self.trailMapView.addTrail(model);
	    	self.collection.add(model);	    
		  });
		  self.trailMapView.updateTrails();
		  self.bMapReady = true;
		  self.playerCheckpoint();
        }
      });        
    },    
    showOverlay: function(){    
      $('#map_overlay_view .back').css('left', -124);
      $('#map_overlay_view .info-hero').css('left', -150);
      $('#map_overlay_view .info-hero .campaign_title').css('left', 189);
      
      $('#view_map_btns').css('top', 32);                                      	          
    },
    playerCheckpoint: function(){
      this.bPlayerReady = true;
	  this.trailMapView.render();
    	
  	  var nRouteID = $.cookie('route_id');          
	  if (nRouteID != undefined) {
	    this.trailMapView.setMapView(new L.LatLng($.cookie('route_lat'), $.cookie('route_lng')), $.cookie('route_zoom'));
        this.trailMapView.selectTrail(nRouteID);
	  	// remove
	  	$.removeCookie('route_id');
	    $.removeCookie('route_lat');
	    $.removeCookie('route_lng');
	    $.removeCookie('route_zoom');        
	  }          
	  this.showOverlay();	  
    },    
    onTrailMapViewZoomInClick: function(mapView){
    },
    onTrailMapViewZoomOutClick: function(mapView){
    },
    onSelectTrail: function(id){
      $('#welcome_view').hide();
      $('#cards_container_view').show();
    	
      var model = this.collection.get(id);
	  this.trailCardView.render(model);
    }
    
  });

  return MapView;
});
