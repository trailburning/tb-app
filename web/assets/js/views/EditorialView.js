define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'views/ActivityFeedView',  
  'views/maps/MapTrailView',
  'views/VideoView'
], function(_, Backbone, TrailModel, ActivityFeedView, MapTrailView, VideoView){

  var STATE_BIG_SPONSOR = 1;
  var STATE_SMALL_SPONSOR = 2;

  var AppView = Backbone.View.extend({
    initialize: function(){
      var self = this;
      
      this.nSponsorState = STATE_BIG_SPONSOR;
      this.nPrevScrollY = 0;
      
      this.nTrailsLoaded = 0;
      this.nTotalTrails = 0;
      
	  $(window).scroll(function () {
        self.handleScroll(); 
      });    
    
	  $(window).bind('touchmove',function(e){
	    self.handleScroll();
	  });    
      
	  $(window).resize(function() {
	    self.handleResize(); 
	  });                
      
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
      this.trailMapView = new MapTrailView({ el: '#editorial_map_view', elCntrls: '#view_map_btns', model: this.model, mapStreet: 'mallbeury.map-kply0zpa', mapMargin: 120 });
	  this.trailMapView.render();	  

	  $('#column_wrapper_intro').columnize({
		columns : 2,
		accuracy : 1,
		lastNeverTallest: true,
		buildOnce : true
	  });
  
	  $('.card_column_wrapper').columnize({
		columns : 2,
		accuracy : 1,
		lastNeverTallest: true,
		buildOnce : true
	  });    

      $('.video_player').each(function(index) {
        this.videoView = new VideoView({ el: this });
        this.videoView.render();
      });

      $('.column_wrapper').css('visibility', 'visible');
      $('#footerview').show();
	  
	  this.handleResize();      
	  this.getResults();
    },
    getResult: function(trail){    	
      var self = this, trailModel = new TrailModel();
      
      trailModel.set('id', trail.id);             
      trailModel.fetch({
        success: function () {        
	      var model = new Backbone.Model(trailModel.get('value').route);
	      
	      self.trailMapView.addTrail(model);
	      self.nTrailsLoaded++;
	      
	      if (self.nTrailsLoaded == self.nTotalTrails) {
	   		$('#editorial_map_view #map_large').show();		  	
  	  		$('#view_map_btns').show();
	      	
		  	self.trailMapView.updateTrails();
		  	self.trailMapView.render();          
	      }
        }      
      });      
    	
    },
    getResults: function(){
      var self = this, model;

      this.nTotalTrails = TB_EDITORIAL_TRAILS.length;

      _.each(TB_EDITORIAL_TRAILS, function (trail) {        
        self.getResult(trail);  	    
      }, this);
    },       
    handleResize: function(){
      $("img.scale_image_ready").imageScale();
    },
	handleScroll: function(){
	  var nTopY = 45;
	  var nTransitionOffY = 35;
	  var nTransitionOnY = 12;
	  var nScrollY = ($(window).scrollTop() < 0) ? 0 : $(window).scrollTop();	  
	  var nFactorY = 2;
	  var bScrollUp = false;

	  // which direction are we scrolling?	  
	  if (nScrollY > this.nPrevScrollY) {
	  	bScrollUp = true;	  	
	  }
	  
      if (Modernizr.touch) {
  	    switch (this.nSponsorState) {
  	      case STATE_BIG_SPONSOR:
  	        if ((nScrollY > $('#big_sponsor_bar').height()) && bScrollUp) {
	  	      this.nSponsorState = STATE_SMALL_SPONSOR;
			  $('#small_sponsor_bar').show();
			  $('#big_sponsor_bar').hide();
  	        }
	  	    break;
	  	    
 		  case STATE_SMALL_SPONSOR:
 		    if ((nScrollY < nTransitionOnY) && !bScrollUp) {
 			  this.nSponsorState = STATE_BIG_SPONSOR;
 			  $('#small_sponsor_bar').hide();
			  $('#big_sponsor_bar').show();
	  	  	}
	  	  	break;  
	     }	  	
	  }
	  else {	  	
	    // move big bar
		$('#big_sponsor_bar').css('top', nTopY - (nScrollY * nFactorY));	
	    
  	    switch (this.nSponsorState) {
  	      case STATE_BIG_SPONSOR:
  	        if ((nScrollY > nTransitionOffY) && bScrollUp) {
	  	      this.nSponsorState = STATE_SMALL_SPONSOR;
	  		  $('#small_sponsor_bar').css('top', nTopY);
			  $('#small_sponsor_bar').css('visibility', 'visible');
  	        }
	  	    break;
	  	    
 		  case STATE_SMALL_SPONSOR:
 		    if ((nScrollY < nTransitionOnY) && !bScrollUp) {
 			  this.nSponsorState = STATE_BIG_SPONSOR;
 			  $('#small_sponsor_bar').css('visibility', 'hidden');
 			  $('#small_sponsor_bar').css('top', 0);
 			}
 		    break;	  	    
	     }	  	
	  }	  
	  this.nPrevScrollY = nScrollY;
	}	
    
  });

  return AppView;
});
