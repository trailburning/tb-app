var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/SearchView',
  'views/HomeHerosView'      
], function(_, Backbone, ActivityFeedView, SearchView, HomeHerosView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
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
  	
	this.searchView = new SearchView({ el: '#searchview' });        
    if (typeof TB_USER_ID != 'undefined') {
  	  this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
  	  this.activityFeedView.render();
  	  this.activityFeedView.getActivity();	  	
    }
        
    this.homeHerosView = new HomeHerosView({ el: '#home_header' });
	this.homeHerosView.render();
	
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
      
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
    
    $('#footerview').show();
  };
    
  return { 
    initialize: initialize
  };   
});  

