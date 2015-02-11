define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView'    
], function (_, Backbone, ActivityFeedView){

  var TourView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      $(window).trigger('resize');

      // animate on step1
      $('#step1_left').removeClass('step1_left_init');
      $('#step1_right').removeClass('step1_right_init');

	  if ($('#step1').length) {
	    this.loadAndShow($('#step1'));      	  	
	  }
	  if ($('#step2').length) {
	    this.loadAndShow($('#step2'));
	  }
	  if ($('#step3').length) {
	    this.loadAndShow($('#step3'));      
	  }
	  if ($('#step4').length) {
	    this.loadAndShow($('#step4'));      
	  }
	  if ($('#step5').length) {
	    this.loadAndShow($('#step5'));         
	  }
	  if ($('#step6').length) {
	    this.loadAndShow($('#step6'));         
	  }

      if (typeof TB_USER_ID != 'undefined') {
  	    this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
  	    this.activityFeedView.render();
  	    this.activityFeedView.getActivity();	  	
      }
    },        
    loadAndShow: function(elContainer){
      var elWaitForImg = $('.fade_on_load', elContainer);      
      var imgLoad1 = imagesLoaded(elWaitForImg);
	  imgLoad1.on('always', function(instance) {
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', elContainer).addClass('tb-fade-in');
        $('.image_container', elContainer).css('opacity', 1);
	  });      
    }    
        
  });

  return TourView;
});
