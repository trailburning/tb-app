define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailSlidePhotoView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlidePhotoViewTemplate').text());        
            
      this.nType = this.options.type;
      this.bLoaded = false;
      this.bRendered = false;
    },            
    show: function(){
      $(this.el).show();
      $('.image_container', $(this.el)).css('opacity', 1);
    },
    hide: function(){
      $('.image_container', $(this.el)).css('opacity', 0);      
    },
    render: function(nPanelWidth){
      var self = this;

      function onImageLoaded() {
        // update pos
        $("img.scale_image_ready", $(self.el)).imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', $(self.el)).addClass('tb-fade');
        // fire event
        app.dispatcher.trigger("TrailSlidePhotoView:imageready", self);                        
      }   
                  
      if (this.bRendered) {
        $('.image_container', this.el).width(nPanelWidth);
        if (self.bLoaded) {
          onImageLoaded();
        }
        return;
      }
                                    
      var versions = this.model.get('versions');
      this.model.set('versionLargePath', versions[0].path);

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      var elImg = $('img', $(this.el));
      var imgLoad = imagesLoaded(elImg);
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        self.bLoaded = true;
        onImageLoaded();
      });


      // register for image ready      
//      $('img', this.el).load(function() {
        // fire event
//        app.dispatcher.trigger("TrailSlidePhotoView:imageready", self);                        
//      });
      // force ie to run the load function if the image is cached
//      if ($('img', this.el).get(0).complete) {
//        $('img', this.el).trigger('load');
//      }
      
      $('.image_container', this.el).width(nPanelWidth);

      this.bRendered = true;
                        
      return this;
    }    
  });

  return TrailSlidePhotoView;
});
