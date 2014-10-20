var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/SearchView',
  'views/HerosView'  
], function(_, Backbone, ActivityFeedView, SearchView, HerosView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    $('#footerview').show();
    
	this.searchView = new SearchView({ el: '#searchview' });
    if (typeof TB_USER_ID != 'undefined') {
  	  this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
  	  this.activityFeedView.render();
  	  this.activityFeedView.getActivity();	  	
    }
    
    if ($('.cookie_error').length) {
      checkCookies();	
    }    

	this.herosView = null;
	if ($('#hero_images').length) {		
      this.herosView = new HerosView({ el: '#hero_images' });
	  this.herosView.render();
	  
      var imgLoad = imagesLoaded('.panel_content .scale, promo_content .scale');
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $(".panel_content img.scale_image_ready, .promo_content img.scale_image_ready").imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.panel_content .fade_on_load, .promo_content .fade_on_load').addClass('tb-fade-in');
        $('.panel_content .image_container, .promo_content .image_container').css('opacity', 1);
      });	  
	}
	else {
      var imgLoad = imagesLoaded('.scale');
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $("img.scale_image_ready").imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load').addClass('tb-fade-in');
        $('.image_container').css('opacity', 1);
      });		
	}

    function checkCookies() {    
	  $.cookie('test', 'trailburning');
	  var strTest = $.cookie('test');
	  if ($.cookie('test') == undefined) {
	  	$('.cookie_error').show();
	  }
	}
    
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
  };
    
  return { 
    initialize: initialize
  };   
});  
