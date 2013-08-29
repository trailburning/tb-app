define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailUploadProgressView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailUploadProgressViewTemplate').text());
    },            
    render: function(nProgress){
      console.log('TrailUploadProgressView:render');
        
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
      
      $('.percent', this.el).html(nProgress);      
      
      return this;
    }
  });

  return TrailUploadProgressView;
});
