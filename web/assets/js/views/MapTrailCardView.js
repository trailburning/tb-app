define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var MapTrailCardView = Backbone.View.extend({
  	className: "panel_content",
    initialize: function(){
      this.template = _.template($('#mapTrailCardViewTemplate').text());        
                        
      this.bRendered = false;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
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
		this.model.set('length_km', Math.round(this.model.get('length') / 1000));
		this.model.set('ascent_m', Math.round(this.model.get('tags').ascent));
		this.model.set('descent_m', Math.round(this.model.get('tags').descent));
      
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        $(this.el).attr('data-id', this.model.id);
        $(this.el).addClass('tb-fade-in-no-delay');     
            
	    $('.link', $(this.el)).click(function(evt){
		  // fire event
          app.dispatcher.trigger("MapTrailCardView:click", self);                	      
	    });

        var nRating = this.model.get('rating');
        if (!nRating) {
          // do not show no stars
       	  $('.stars', $(this.el)).hide();
        }
        
        $.each($('.star', $(this.el)), function(index, value){        	
		  switch (index) {
          	case 0:
          	  if (nRating > 0) {
          	    $(this).addClass('star_full');          	  	
          	  }
          	  break;
          	case 1:
          	  if (nRating >= 2) {
          	    $(this).addClass('star_full');          	  	          	  
          	  }
          	  else if (nRating >= 1.5) {
          	    $(this).addClass('star_half');          	  	          	  
          	  }
          	  else {
          	    $(this).addClass('star');          	  	          	  
          	  }
          	  break;
          	case 2:
          	  if (nRating >= 3) {
          	    $(this).addClass('star_full');          	  	          	  
          	  }
          	  else if (nRating >= 2.5) {
          	    $(this).addClass('star_half');          	  	          	  
          	  }
          	  else {
          	    $(this).addClass('star');          	  	          	  
          	  }
          	  break;
          	case 3:
          	  if (nRating >= 4) {
          	    $(this).addClass('star_full');          	  	          	  
          	  }
          	  else if (nRating >= 3.5) {
          	    $(this).addClass('star_half');          	  	          	  
          	  }
          	  else {
          	    $(this).addClass('star');          	  	          	  
          	  }
          	  break;
          	case 4:
          	  if (nRating >= 5) {
          	    $(this).addClass('star_full');          	  	          	  
          	  }
          	  else if (nRating >= 4.5) {
          	    $(this).addClass('star_half');          	  	          	  
          	  }
          	  else {
          	    $(this).addClass('star');          	  	          	  
          	  }
          	  break;
          }        	
        });      
	  }
	  
      $('.location', this.el).click(function(evt){
		// fire event
        app.dispatcher.trigger("MapTrailCardView:cardmarkerclick", self);                
      });

      $('.location', this.el).mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
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
	  
      this.bRendered = true;
                       
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

  return MapTrailCardView;
});
