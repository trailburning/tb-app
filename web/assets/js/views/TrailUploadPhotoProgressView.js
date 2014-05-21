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
        // set upload text
        var elTitle = $('.msg', $(this.el));
        elTitle.text(elTitle.attr('data-singular'));
        if (this.options.bMultiUpload) {
          elTitle.text(elTitle.attr('data-plural'));
        }
      }
      $('.progressbar', this.el).progressbar({value: nProgress});      
      $('.percent', this.el).html(nProgress);      
      
      this.bRendered = true;
      
      return this;
    }
  });

  return TrailUploadPhotoProgressView;
});
