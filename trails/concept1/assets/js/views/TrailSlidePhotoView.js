define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailSlidePhotoView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlidePhotoViewTemplate').text());        
            
      this.bRendered = false;
    },            
    show: function(){
      $(this.el).show();
      $('.slide_container, .image_container', $(this.el)).css({ opacity: 1 });
    },
    hide: function(){
      $('.slide_container, .image_container', $(this.el)).css({ opacity: 0 });
    },
    render: function(nPanelWidth){
      var self = this;
      
      if (this.bRendered) {
        $('.image_container', this.el).width(nPanelWidth);
        return;
      }
                                    
      var versions = this.model.get('versions');
      this.model.set('versionLargePath', versions[0].path);

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      // register for image ready      
      $('img', this.el).load(function() {
        // fire event
        app.dispatcher.trigger("TrailSlidePhotoView:imageready", self);                        
      });
      // force ie to run the load function if the image is cached
      if ($('img', this.el).get(0).complete) {
        $('img', this.el).trigger('load');
      }
      
      $('.image_container', this.el).width(nPanelWidth);
                        
      this.bRendered = true;
                        
      return this;
    }    
  });

  return TrailSlidePhotoView;
});
