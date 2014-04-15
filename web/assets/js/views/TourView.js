define([
  'underscore', 
  'backbone'
], function (_, Backbone){

  var TourView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      $(window).trigger('resize');

      // animate on step1
      $('#step1_left').removeClass('step1_left_init');
      $('#step1_right').removeClass('step1_right_init');

	  this.loadAndShow($('#step1'));      
	  this.loadAndShow($('#step2'));
	  this.loadAndShow($('#step3'));      
	  this.loadAndShow($('#step4'));      
	  this.loadAndShow($('#step5'));            
    },        
    loadAndShow: function(elContainer){
      var self = this;
      
      var elWaitForImg = $('.fade_on_load', elContainer);      
      var imgLoad1 = imagesLoaded(elWaitForImg);
	  imgLoad1.on('always', function(instance) {
        for ( var i = 0, len = imgLoad1.images.length; i < len; i++ ) {
          $(imgLoad1.images[i].img).addClass('scale_image_ready');
        }
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', elContainer).addClass('tb-fade-in');
        $('.image_container', elContainer).css('opacity', 1);
	  });      
    }    
        
  });

  return TourView;
});
