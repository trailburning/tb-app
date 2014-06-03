define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var MapTrailCardView = Backbone.View.extend({
  	className: "panel_content",
    initialize: function(){
      this.template = _.template($('#mapTrailCardViewTemplate').text());        
                        
      this.bRendered = false;
      this.hideTimer = null;
    },            
    render: function(){
      var self = this;

      if (!this.bRendered) {
      	// mla - all avatar urls should be http based
      	if (this.model.get('user').avatar.substr(0, 4) != 'http') {
      	  this.model.get('user').avatar = 'http://s3-eu-west-1.amazonaws.com/trailburning-assets/images/default/' + this.model.get('user').avatar;
      	}
      		
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
      
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        $(this.el).attr('data-id', this.model.id);
        $(this.el).removeClass('move');     
            
	    $('.link', $(this.el)).click(function(evt){
		  // fire event
          app.dispatcher.trigger("MapTrailCardView:click", self);                	      
	    });
      
        var nRating = this.model.get('rating');
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
    init: function(bMoveForward){
      var nY =  500;
	  if (!bMoveForward) {
	    nY =  -500;	
	  }    	

      $(this.el).removeClass('move');     
      $(this.el).css('top', nY);    	
    },
    show: function(){
      if (this.hideTimer) {
        clearTimeout(this.hideTimer);      	
      } 
      $(this.el).addClass('move');
	  // invoke resrc      
      resrc.resrc($('.scale', $(this.el)));                
      $(this.el).css('top', 0);
    },
    hide: function(bMoveForward){
      var nY =  -500;
	  if (!bMoveForward) {
	    nY =  500;	
	  }    	
    	
      var self = this;
      $(this.el).css('top', nY);
      
	  this.hideTimer = setTimeout(function() {
	  	$(self.el).removeClass('move');
	  	$(self.el).remove();
	  }, 1000);            
    }

  });

  return MapTrailCardView;
});
