define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var AppView = Backbone.View.extend({
    initialize: function(){
	  var self = this;
	  
	  $('.royalSlider').show();

  	  $(".royalSlider").royalSlider({
  	  	imageScaleMode: 'fill',
  	  	controlNavigation: 'none',
  	  	slidesSpacing: 0,
  	  	loop: true,
//  	  	transitionType: 'fade',
        keyboardNavEnabled: true,
        autoScaleSlider: false,
    	fullscreen: {
    		enabled: true,
    		nativeFS: false
    	}
      });  	
      
	  var slider = $(".royalSlider").data('royalSlider');
//      slider.enterFullscreen();

	  slider.ev.on('rsAfterSlideChange', function(event) {
	    // triggers after slide change
	    
	    console.log(slider.currSlide);
	  });
  	  
	}
	
  });

  return AppView;
});
