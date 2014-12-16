define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/HomeHerosView',
  'views/TwitterView'  
], function(_, Backbone, ActivityFeedView, HomeHerosView, TwitterView){
  
  var HomeView = Backbone.View.extend({
    initialize: function(){
      var self = this;
        
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
        
	  $('.discover_content .scale, .trails_content .scale').imagesLoaded()
  	    .progress( function(instance, image) {
  	  	  $(image.img).addClass('scale_image_ready');
          // update pos
          $(image.img).imageScale();
  	  	
    	  var elContainer = $(image.img).parent();
    	  if (elContainer.hasClass('fade_on_load')) {
            // fade in - delay adding class to ensure image is ready  
            elContainer.addClass('tb-fade-in');
            elContainer.css('opacity', 1);    		
    	  }
		  // invoke resrc      
	      resrc.resrc($(image.img));        
  	  });    
  	
      this.homeHerosView = new HomeHerosView({ el: '#home_header' });
	  this.homeHerosView.render();
	
	  var strTwitterUser = "trailburning";
      this.twitterView = new TwitterView({ el: '#twitter_view', model: this.model, user: strTwitterUser, bShowRetweets: true });
      this.twitterView.getResults();            
	
      $(window).resize(function() {
        self.handleResize(); 
      });    
      this.handleResize();        
	
  	  // keyboard control
  	  $(document).keydown(function(e){
  	    switch (e.keyCode) {
  	      case 37: // previous hero
  	        self.homeHerosView.prevHero();
  	        break;
  	  	  case 39: // next hero
  	        self.homeHerosView.nextHero();
  	        break;
  	    }
  	  });
    
      $('#footerview').show();
    },
    handleResize: function(){
      $("img.scale_image_ready").imageScale();
	}    
    
  });

  return HomeView;
});
