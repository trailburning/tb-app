define([
  'underscore', 
  'backbone',
  'views/maps/MapTrailView'
], function(_, Backbone, MapTrailView){
  
  var PLAYER_INTRO = 0;
  var PLAYER_SHOW = 1;  
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;
  
  var CampaignPlayerView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.nPlayerView = PLAYER_INTRO;
	  // do we have a route to select?
	  var nRouteID = $.cookie('route_id');
	  if (nRouteID != undefined) {
        this.nPlayerView = PLAYER_SHOW;
	  }

      this.nTrailView = SLIDE_VIEW;

      this.bLocked = true;
      this.collection = new Backbone.Collection();
      this.slideTimer = null;
      this.nCurrSlide = -1;
      this.bMapReady = false;
      this.bPlayerReady = false;  
      this.nPlayerMinHeight = $('#campaignplayer').height();

      this.nPlayerHeight = 0;

      app.dispatcher.on("TrailMapView:selecttrail", self.onSelectTrail, this);
      app.dispatcher.on("TrailMapView:zoominclick", self.onTrailMapViewZoomInClick, this);
      app.dispatcher.on("TrailMapView:zoomoutclick", self.onTrailMapViewZoomOutClick, this);

      this.trailMapView = new MapTrailView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });

	  this.getResults();
	  this.buildBtns();
    },
    buildBtns: function(){
      var self = this;
      
      $('#player_big_btn').click(function(evt){
      	self.showPlayer();
	  });

      $('#player_big_btn').mouseover(function(evt){
      	$(evt.currentTarget).css('cursor','pointer');
	  });
      
      $('#trail_mini_view .toggle_btn').click(function(evt){
        self.onTrailToggleViewBtnClick(evt);
	  });

      $('#trail_mini_view .toggle_btn').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });
      
      $('#campaign_landing_overlay_view .campaign_play').click(function(evt){
      	self.showPlayer();
	  });

      $('#campaign_landing_overlay_view .campaign_play').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });

      $('#view_player_btns .close_btn').click(function(evt){
      	self.hidePlayer();
	  });

      $('#view_player_btns .close_btn').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
	  });	  
	},    
    render: function(){
  	  this.trailMapView.render();        
	},
	handleResize: function(){
      // remove transition to avoid seeing grey beneath image when resizing
      $('#campaignplayer').removeClass('tb-size');
      $('#trail_views').removeClass('tb-move-vert');
      
      this.updatePlayerHeight();
      $('#campaignplayer .map_container').width($('#appview').width());
            
      this.trailMapView.render();
	},
    updatePlayerHeight: function(){
      var nMapHeight = 0;      
      var nPlayerViewerHeight = 0;
      
      var elContentView = $('#content_view');
      var nContentY = elContentView.position().top;
      var nContentHeight = $(window).height() - nContentY;
  	  if (nContentHeight < this.nPlayerMinHeight) {
  	  	nContentHeight = this.nPlayerMinHeight;
	  }
      
	  switch (this.nPlayerView) {
	  	case PLAYER_INTRO:
	  	  nPlayerViewerHeight = this.nPlayerMinHeight;
	  	  this.nPlayerHeight = nContentHeight;

	  	  $('#trail_views').css('top', 0);
	  	  if (nContentHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nContentHeight - nPlayerViewerHeight)/2;
            $('#trail_views').css('top', -nAdjustY);        
	  	  } 
	  	  break;
	  	
	  	case PLAYER_SHOW:
	  	  nPlayerViewerHeight = nContentHeight;
      	  this.nPlayerHeight = nContentHeight;
	  	  
	  	  if (nContentHeight > nPlayerViewerHeight) {
	  	  	var nAdjustY = (nContentHeight - nPlayerViewerHeight)/2;
            $('#trail_views').css('top', -nAdjustY);            
            $('#trail_map_view').css('top', nAdjustY);                    	  	  	
	  	  }
	  	  		  	  
	  	  if (nMapHeight < nPlayerViewerHeight) {
	  	  	// player is smaller than viewer
	  	    nPlayerViewerHeight = nContentHeight;
	  	    $('#trail_views').css('top', 0);	  	  	
		  }
	  	  break;
	  }

      $('#campaignplayer').height(nPlayerViewerHeight);
      
   	  $('#trail_map_view').height(nContentHeight);
   	  // force height update for MapBox
   	  $('#trail_map_view .map_container').height(nContentHeight);      	  	  
    },    
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?campaign_id=1&limit=500&offset=0';
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
    handleMedia: function(){
      var self = this;
      
      $('#trail_overlay').addClass('tb-move-vert');
      $('#trail_info').addClass('tb-move-vert');
      $('#trail_info .trail_avatar').addClass('tb-move-vert');       
      $('#trail_info .trail_title').addClass('tb-move-vert'); 
      $('#view_player_btns').addClass('tb-move-vert');
      $('#view_map_btns').addClass('tb-move-vert');
      
      this.bPlayerReady = true;
    },        
    togglePlayer: function(){
      switch (this.nPlayerView) {
	    case PLAYER_INTRO:
	      this.showPlayer();
      	  break;
      		
	 	case PLAYER_SHOW:
	  	  this.hidePlayer();
      	  break;
      }
    },
    showPlayer: function(){
      if (this.bLocked) {
    	return;
      }
      this.bLocked = true;
      
	  var self = this;

	  $('#player_big_btn').hide();
      // add transition for effect      
      $('#campaignplayer').addClass('tb-size');
	  $('#trail_views').addClass('tb-move-vert');

      this.showMapView();
      
      this.hideIntroOverlay();

      self.nPlayerView = PLAYER_SHOW;
      self.updatePlayerHeight();
	  self.bLocked = false;
            
      $('#view_player_btns').css('top', 18);
    },
    hidePlayer: function(){
      if (this.bLocked) {
    	return;
      }
      this.bLocked = true;
      	
	  var self = this;
      	
      this.nPlayerView = PLAYER_INTRO;

	  $('#player_big_btn').show();
	  $('#headerview .close_link').hide();

      // add transition for effect      
      $('#campaignplayer').addClass('tb-size');
	  $('#trail_views').addClass('tb-move-vert');
      
      this.updatePlayerHeight();
      
      $('#view_player_btns').css('top', -160);

	  self.showPhotoView();      
      self.showIntroOverlay();
      self.bLocked = false;
    },
    showIntroOverlay: function(){
      $('#campaign_landing_overlay_view .back').css('left', 0);
      $('#campaign_landing_overlay_view .info-hero').css('left', -144);
      $('#campaign_landing_overlay_view .info-hero .campaign_title').css('left', 189);                                	          
      
      $('#campaign_map_overlay_view .back').css('left', -800);
      $('#campaign_map_overlay_view .info-hero').css('left', -800);
      $('#campaign_map_overlay_view .info-hero .campaign_title').css('left', -100);
    },
    hideIntroOverlay: function(){    
      $('#campaign_landing_overlay_view .back').css('left', -800);
      $('#campaign_landing_overlay_view .info-hero').css('left', -800);
      $('#campaign_landing_overlay_view .info-hero .campaign_title').css('left', -100);
      
      $('#campaign_map_overlay_view .back').css('left', -124);
      $('#campaign_map_overlay_view .info-hero').css('left', -150);
      $('#campaign_map_overlay_view .info-hero .campaign_title').css('left', 189);                                	          
    },
    toggleView: function(){
      if (this.nPlayerView != PLAYER_SHOW) {
      	return;
      }
    	
      this.onTrailToggleViewBtnClick();
    }, 
    showNextSlide: function(){
      if (this.nPlayerView != PLAYER_SHOW) {
      	return;
      }

      this.stopSlideShow();
	  this.nextSlide();         
    },          
    nextSlide: function(){
      var nSlide = this.nCurrSlide; 
      if (nSlide < this.mediaCollection.length-1) {
        nSlide++;                               
      }
      else {
        nSlide = 0;
      }
      this.gotoMedia(nSlide);
    },   
    showPrevSlide: function(){
      if (this.nPlayerView != PLAYER_SHOW) {
      	return;
      }
    	
      this.stopSlideShow();
	  this.prevSlide();         
    },   
    prevSlide: function(){
      var nSlide = this.nCurrSlide; 
      if (nSlide > 0) {
        nSlide--;                               
      }
      else {
        nSlide = this.mediaCollection.length-1;
      }
      this.gotoMedia(nSlide);
    },    
    gotoMedia: function(nSlide){          
      this.trailSlidesView.gotoSlide(nSlide);
      this.trailMiniSlidesView.gotoSlide(nSlide);
	  this.trailStatsView.setCurrSlide(nSlide+1);
      
      this.trailMiniMapView.gotoMedia(nSlide);
      this.trailMapView.gotoMedia(nSlide);
      
      this.trailAltitudeView.gotoMedia(nSlide);
      
      this.nCurrSlide = nSlide;    
      
      // render next slide to avoid stalling when in slide show
      if (nSlide < this.mediaCollection.length-1) {            
        this.trailSlidesView.render(nSlide+1);
        this.trailMiniSlidesView.render(nSlide+1);
      }              
      this.updatePlayerHeight();      
    },
    showMapView: function(evt){
      if (this.nTrailView == MAP_VIEW) {
      	return;
      }
    	
      this.nTrailView = MAP_VIEW;
      
      $('#view_map_btns').css('top', 18);
      
      $('#view_toggle .button').addClass('view_photo');
      $('#view_toggle .button').removeClass('view_map');
      if (evt) {
        if (!Modernizr.touch && $(evt.currentTarget).attr('id') == 'view_toggle_btn') {
          $('#view_toggle .button').addClass('view_photo_hover');
        }        
      }
    },
    showPhotoView: function(evt){
      if (this.nTrailView == SLIDE_VIEW) {
      	return;
      }
    	
      this.nTrailView = SLIDE_VIEW;
      
      $('#view_map_btns').css('top', -300);
      
      $('#view_toggle .button').addClass('view_map');
      $('#view_toggle .button').removeClass('view_photo');
      if (evt) {
        if (!Modernizr.touch && $(evt.currentTarget).attr('id') == 'view_toggle_btn') {
          $('#view_toggle .button').addClass('view_map_hover');        
        }
      }
      this.trailMapView.unselectTrail();
    },
    playerCheckpoint: function(){
      if (!this.bMapReady) {
      	return;
      }
      
      var self = this;
      this.bLocked = false;
        
      this.trailMapView.show();
      
      this.handleResize();      
        
      switch (this.nPlayerView) {
        case PLAYER_INTRO:
          setTimeout(function() {
			self.showIntroOverlay();
          }, 1000);
          break;
            
        case PLAYER_SHOW:
          this.showPlayer();
          
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
          break;
      }
    },    
    onTrailStatsPlayClick: function(){
      this.startSlideShow(); 
    },
    onTrailStatsPauseClick: function(){   
      this.stopSlideShow(); 
    },
    onTrailMapViewZoomInClick: function(mapView){
    },
    onTrailMapViewZoomOutClick: function(mapView){
    },
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      // look up model in collcetion
      var nMedia = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      this.stopSlideShow();
      this.gotoMedia(nMedia);
    },
    onTrailMapMediaPhotoClick: function(mapMediaMarkerView){
      this.toggleView();
    },
    onTrailMediaMarkerClick: function(mediaMarkerView){
      // look up model in collcetion
      var nMedia = this.mediaCollection.indexOf(mediaMarkerView.model);
      
      this.stopSlideShow();
      this.gotoMedia(nMedia);
    },
    onTrailToggleViewBtnOver: function(evt){
      $(evt.currentTarget).css('cursor','pointer');      
      
      if (Modernizr.touch) {
        return;
      }
      
      switch (this.nTrailView) {
        case MAP_VIEW:
          $('#view_toggle .button').addClass('view_photo_hover');        
          break;
          
        case SLIDE_VIEW:
          $('#view_toggle .button').addClass('view_map_hover');        
          break;
      }
    },
    onTrailToggleViewBtnOut: function(evt){
      if (Modernizr.touch) {
        return;
      }
      
      switch (this.nTrailView) {
        case MAP_VIEW:
          $('#view_toggle .button').removeClass('view_photo_hover');        
          break;
          
        case SLIDE_VIEW:
          $('#view_toggle .button').removeClass('view_map_hover');        
          break;
      }
    },
    onTrailToggleViewBtnClick: function(evt){
      $('#view_toggle .button').removeClass('view_photo_hover');        
      $('#view_toggle .button').removeClass('view_map_hover');        
      
      switch (this.nTrailView) {
        case MAP_VIEW:
          this.showPhotoView(evt);
          break;
          
        case SLIDE_VIEW:
          this.showMapView(evt);
          break;
      }
      this.handleResize();      
    },
    onSelectTrail: function(id){
    }
    
  });

  return CampaignPlayerView;
});
