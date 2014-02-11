define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailSlideshowSlideView = Backbone.View.extend({
  	className: "slide",
    initialize: function(){
      this.template = _.template($('#slideshowSlideViewTemplate').text());
    },            
    render: function(){
      var self = this;
	  this.bRendered = false;

      // first time
      if (!this.bRendered) {
        var versions = this.model.get('versions');
        this.model.set('versionLargePath', versions[0].path);
      	
	    var attribs = this.model.toJSON();
	    $(this.el).html(this.template(attribs));
	    	    
		// scale images when loaded
        var elImages = $('.scale', $(this.el));	    
	    var imgLoad = imagesLoaded(elImages);
        imgLoad.on('always', function(instance) {
          for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
            $(imgLoad.images[i].img).addClass('scale_image_ready');
          }
          // update pos
          $("img.scale_image_ready", $(self.el)).imageScale();
          // fade in - delay adding class to ensure image is ready  
          $('.fade_on_load', $(self.el)).addClass('tb-fade-in');
          $('.image_container', $(self.el)).css('opacity', 1);
        });
	    
		// store id for reference	    
	    $(this.el).attr("data-id", this.model.id);
	    
	    $(this.el).mouseover(function(evt){
          $(evt.currentTarget).css('cursor','pointer');      
        });      
	    
	    $(this.el).click(function(evt){
          // fire event
          app.dispatcher.trigger("TrailSlideshowSlideView:click", self);                
        });      
	  }
      this.bRendered = true;

      return this;
    }
    
  });

  return TrailSlideshowSlideView;
});
