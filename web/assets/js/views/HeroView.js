define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var HeroView = Backbone.View.extend({
    initialize: function(){
      this.nPos = this.options.pos;
      this.Active = false;
    },   
    render: function(){
      var self = this;
            
      return this;    	
	},
    load: function(){
      var self = this;
    	
      var elImages = $('.image_container', $(this.el));
      var imgLoad = imagesLoaded(elImages);
	  imgLoad.on('always', function(instance) {
	    for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
	      if ($(imgLoad.images[i].img).hasClass('scale')) {
	  	    $(imgLoad.images[i].img).addClass('scale_image_ready');	      	
	      }
	   	}	  		
        // fire event
        app.dispatcher.trigger("HeroView:ready", self);                        
	  });
    },
    show: function(){
      this.Active = true;
      var self = this;

	  $(this.el).css('visibility', 'visible');

	  // update pos
	  $('img.scale_image_ready', $(this.el)).imageScale();
	  // fade in - delay adding class to ensure image is ready  
	  $('.fade_on_load_wait', $(this.el)).addClass('tb-fade-in-no-delay');
	  $('.image_container', $(this.el)).css('opacity', 1);
	  // force update to fix blurry bug
	  resrc.resrcAll();
    },	
    hide: function(){
      this.Active = false;
	  $('.image_container', $(this.el)).css('opacity', 0);
	},
  });

  return HeroView;
});
