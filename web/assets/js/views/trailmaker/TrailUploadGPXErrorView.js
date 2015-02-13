define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadGPXErrorView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadGPXErrorViewTemplate').text());
      
      this.bRendered = false;
    },            
    render: function(nProgress){
      if (!this.bRendered) {
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
      }      
      this.bRendered = true;
      
      return this;
    }
  });

  return TrailUploadGPXErrorView;
});
