var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/UserView',
  'views/SearchView'  
], function(_, Modernizr, Backbone, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';
  	        
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();
    
    this.appView = new AppView({ el: '#appview' });
    
	this.searchView = new SearchView({ el: '#searchview' });

	$('.scale').imagesLoaded()
  	  .progress( function(instance, image) {
  	  	$(image.img).addClass('scale_image_ready');
        // update pos
        $(image.img).imageScale();
  	  	
    	var elContainer = $(image.img).parent();
    	if (elContainer.hasClass('fade_on_load')) {
          // fade in - delay adding class to ensure image is ready  
          elContainer.addClass('tb-fade-in');
		  var nRnd = 100 * (Math.floor(Math.random() * 6) + 1);
		  setTimeout(function(){
		  	elContainer.css('opacity', 1);
		  }, nRnd);
    	}
		// invoke resrc      
	    resrc.resrc($(image.img));        
  	});    
    
  	$('#footerview').show();  	
  	    
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }    
  };
    
  return { 
    initialize: initialize
  };   
});  
