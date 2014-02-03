define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadPhotoProgressView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadPhotoProgressViewTemplate').text());
    },            
    render: function(nProgress){
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      $( ".progressbar" ).progressbar({
        value: nProgress
      });
      
      $('.percent', this.el).html(nProgress);      
      
      return this;
    }
  });

  return TrailUploadPhotoProgressView;
});
