define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var MapTrailEventCardView = Backbone.View.extend({
  	className: "panel_content",
    initialize: function(){
      this.template = _.template($('#mapTrailEventCardViewTemplate').text());        
                        
      this.bRendered = false;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
      	if (this.model) {
          var versions = this.model.get('media').versions;
      	  this.model.set('versionLargePath', versions[0].path);
		  if (this.model.get('category') == undefined) {
		  	this.model.set('category', '');
      	  }
      	        	  
          var versions = this.model.get('media').versions;
      	  this.model.set('versionLargePath', versions[0].path);
		  if (this.model.get('category') == undefined) {
		  	this.model.set('category', '');
      	  }
      	}

        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        $(this.el).attr('data-id', this.model.id);
        $(this.el).addClass('tb-fade-in-no-delay');
        
	    $('.link', $(this.el)).click(function(evt){
		  // fire event
          app.dispatcher.trigger("MapTrailEventCardView:click", self);                	      
	    });        
 	  }
      this.bRendered = true;
                       
      $('.location', this.el).click(function(evt){
		// fire event
        app.dispatcher.trigger("MapTrailCardView:cardmarkerclick", self);                
      });
                       
      $('.fade_on_load', $(self.el)).removeClass('tb-fade-in');
      $('.image_container', $(self.el)).css('opacity', 0);
	  
      var imgLoad = imagesLoaded($('.scale', $(this.el)));
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
          // update pos
          $(imgLoad.images[i].img).imageScale();
        }
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', $(self.el)).addClass('tb-fade-in');
        $('.image_container', $(self.el)).css('opacity', 1);
      });        
	  // invoke resrc      
      resrc.resrc($('.scale', $(this.el)));                
                       
      return this;
    },
    show: function(){
	  // invoke resrc      
      resrc.resrc($('.scale', $(this.el)));
      $(this.el).css('opacity', 1);                
    },
    hide: function(){
	  $(this.el).css('opacity', 0);
    }

  });

  return MapTrailEventCardView;
});
