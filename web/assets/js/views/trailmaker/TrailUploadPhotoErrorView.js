define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadPhotoErrorView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadPhotoErrorViewTemplate').text());
      
      this.bRendered = false;
    },            
    render: function(nProgress){
      var self = this;
      
      if (!this.bRendered) {
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));      	
      }
      
      $('.next_step a', $(this.el)).click(function(){
	    // fire event
	    app.dispatcher.trigger("TrailUploadPhotoErrorView:closeclick", self);                
      });
      
      this.bRendered = true;
      
      return this;
    }
  });

  return TrailUploadPhotoErrorView;
});
