define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var OverlayView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#overlayViewTemplate').text());
    },            
    render: function(){
      var self = this;
      
      $(this.el).html(this.template());

	  $(this.el).click(function(evt){
 		if (!($('#tb-overlay-view .panel').is(':hover') || 
 		    $('#tb-overlay-view .tb-step-marker .photo').is(':hover'))) {
          // fire event
          app.dispatcher.trigger("OverlayView:close", self);                          
    	}  	
	  	
	  });

      return this;
	}	
    
  });

  return OverlayView;
});
