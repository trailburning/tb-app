define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var OverlayView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#overlayViewTemplate').text());
    },            
    render: function(){
      $(this.el).html(this.template());

      return this;
	}	
    
  });

  return OverlayView;
});
