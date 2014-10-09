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
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    var imgLoad1 = imagesLoaded('.discover_content .scale');
	imgLoad1.on('always', function(instance) {
      for ( var i = 0, len = imgLoad1.images.length; i < len; i++ ) {
        $(imgLoad1.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $('.discover_content img.scale_image_ready').imageScale();
      // fade in - delay adding class to ensure image is ready  
      $('.discover_content .fade_on_load').addClass('tb-fade-in');
      $('.discover_content .image_container').css('opacity', 1);
      // force update to fix blurry bug
	  resrc.resrcAll();
	});
        
    var imgLoad2 = imagesLoaded('.trails_content .scale');
	imgLoad2.on('always', function(instance) {
      for ( var i = 0, len = imgLoad2.images.length; i < len; i++ ) {
        $(imgLoad2.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $('.trails_content img.scale_image_ready').imageScale();
      // fade in - delay adding class to ensure image is ready  
      $('.trails_content .fade_on_load').addClass('tb-fade-in');
      $('.trails_content .image_container').css('opacity', 1);
      // force update to fix blurry bug
	  resrc.resrcAll();
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

