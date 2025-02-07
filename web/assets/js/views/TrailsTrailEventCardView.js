define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailsTrailEventCardView = Backbone.View.extend({
  	className: "panel",
    initialize: function(){
      this.template = _.template($('#trailsTrailEventCardViewTemplate').text());        
            
      this.bRendered = false;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
      	if (this.model) {
          var versions = this.model.get('media').versions;
      	  this.model.set('versionLargePath', versions[0].path);	
      	}
		this.model.set('length_km', Math.ceil(this.model.get('length') / 1000));
		this.model.set('ascent_m', formatAltitude(Math.round(this.model.get('tags').ascent)));
		this.model.set('descent_m', formatAltitude(Math.round(this.model.get('tags').descent)));

        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        $(this.el).addClass('trail_card_panel');
      
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
	  }
      this.bRendered = true;
                       
      return this;
    }    
  });

  return TrailsTrailEventCardView;
});
