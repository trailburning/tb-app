define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlideViewTemplate').text());        
            
      this.nType = this.options.type;
      this.bLoaded = false;
      this.bRendered = false;
      this.bLandscape = true;
    },            
    isLoaded: function(){
      return this.bLoaded;
    },    	
    show: function(nPanelWidth){
      $(this.el).show();
            
      $('.image_container', $(this.el)).width(nPanelWidth);
	  // force scale      
      $("img.scale_image_ready", $(this.el)).imageScale();
      $('.image_container', $(this.el)).css('opacity', 1);
    },
    hide: function(){
      $('.image_container', $(this.el)).css('opacity', 0);      
    },
    render: function(){
      var self = this;

      if (this.bRendered) {
        return;
      }

      var versions = this.model.get('versions');
      this.model.set('versionLargePath', versions[0].path);

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      var tags = this.model.get('tags');
	  // detect portrait
	  if (Number(tags.height) > Number(tags.width)) {
	  	this.bLandscape = false;
	  	
	  	$('.background', $(this.el)).remove();	  	
	  }
	  else {
	  	// remove foreground
	  	$('.foreground', $(this.el)).remove();
	  	$('.background_blur', $(this.el)).remove();
	  }
	  // force resrc update
	  // 154.10.11 - mla not sure that we need this.  Having it makes image load now.
//	  resrc.resrc($('img.resrc', $(this.el)));	        

	  this.bRendered = true;

      return this;
    },
    load: function(){
      var self = this;
      
      var elImg = $('img', $(this.el));
      var imgLoad = imagesLoaded(elImg);
	  imgLoad.on('always', function(instance) {
	    for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
	      // do we want to scale?
	      if ($(imgLoad.images[i].img).hasClass('scale')) {
	  	    $(imgLoad.images[i].img).addClass('scale_image_ready');	      	
	      }
	   	}	  			   	
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', $(self.el)).addClass('tb-fade');
	   	
		self.bLoaded = true;
	   	
        // fire event
        app.dispatcher.trigger("TrailSlideView:imageready", self);                              
	  });      
	  // force resrc update
	  resrc.resrc($('img.resrc', $(this.el)));	        
	}
	        
  });

  return TrailSlideView;
});
