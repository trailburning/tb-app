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
		// store id for reference	    
	    $(this.el).attr("data-id", this.model.id);
	    
	    $(this.el).mouseover(function(evt){
          $(evt.currentTarget).css('cursor','pointer');      
        });      
	    
	    $(this.el).click(function(evt){
	    	console.log('id:'+self.model.id);
	    	
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
