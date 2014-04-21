define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadPhotoProgressView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadPhotoProgressViewTemplate').text());
      
      this.bRendered = false;
    },            
    render: function(nProgress){
      if (!this.bRendered) {
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));      	
        $(this.el).html(this.template());      	
      }
      $('.progressbar', this.el).progressbar({value: nProgress});      
      $('.percent', this.el).html(nProgress);      
      
      this.bRendered = true;
      
      return this;
    }
  });

  return TrailUploadPhotoProgressView;
});
