define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlideViewTemplate').text());        
            
      this.nType = this.options.type;
      this.nPanelWidth = 0;
      this.bLoaded = false;
      this.bRendered = false;
    },            
    isLoaded: function(){
      return this.bLoaded;
    },    	
    show: function(){
      $(this.el).show();
	  // force scale      
      $("img.scale_image_ready", $(this.el)).imageScale();
      $('.image_container', $(this.el)).css('opacity', 1);
    },
    hide: function(){
      $('.image_container', $(this.el)).css('opacity', 0);      
    },
    render: function(nPanelWidth){
      this.nPanelWidth = nPanelWidth;      	
      
      var self = this;

      if (this.bRendered) {
        return;
      }

      var versions = this.model.get('versions');
      this.model.set('versionLargePath', versions[0].path);

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

	  // force resrc update
	  resrc.resrc($('img', $(this.el)));

	  this.bRendered = true;

      return this;
    },
    load: function(){
      var self = this;
      
      var elScale = $('.scale', $(this.el));
      var imgLoad = imagesLoaded(elScale);
	  imgLoad.on('always', function(instance) {
	    for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
	  	  $(imgLoad.images[i].img).addClass('scale_image_ready');
	   	}	  			   	
        $('.image_container', self.el).width(self.nPanelWidth);
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', $(self.el)).addClass('tb-fade');
	   	
		self.bLoaded = true;
	   	
        // fire event
        app.dispatcher.trigger("TrailSlideView:imageready", self);                        
	  });      
	}
	        
  });

  return TrailSlideView;
});
