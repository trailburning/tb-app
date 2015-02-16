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
	}
		
    $('img').imagesLoaded()
      .progress( function(instance, image) {
	    if ($(image.img).hasClass('scale')) {
  	      $(image.img).addClass('scale_image_ready');
          $(image.img).imageScale();
        }

	    var elContainer = $(image.img).closest('.image_container');
	    if (elContainer.hasClass('fade_on_load')) {
          elContainer.addClass('tb-fade-in');
	      elContainer.css('opacity', 1);
	    }
    });    
	
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
