define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView'    
], function(_, Backbone, ActivityFeedView){

  var STATE_BIG_SPONSOR = 1;
  var STATE_SMALL_SPONSOR = 2;

  var AppView = Backbone.View.extend({
    initialize: function(){
      var self = this;
      
      this.nSponsorState = STATE_BIG_SPONSOR;
      this.nPrevScrollY = 0;
      
	  $(window).scroll(function () {
        self.handleScroll(); 
      });    
    
	  $(window).bind('touchmove',function(e){
	    self.handleScroll();
	  });    
      
	  $(window).resize(function() {
	    self.handleResize(); 
	  });                
      
      $('#footerview').show();
	  
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
	  
	  this.handleResize();      
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
