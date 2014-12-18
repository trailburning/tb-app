define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var OFFSCREEN_Y = 500;

  var HomeHeroView = Backbone.View.extend({
    initialize: function(){
      this.elOverlay = this.options.elOverlay;
      this.nPos = this.options.pos;
      this.Active = false;
    },   
    render: function(){
      var self = this;
      
      this.elHeroLeft = $('.left', $(this.elOverlay));
      this.elHeroRight = $('.right', $(this.elOverlay));
      this.elCredit = $('.tb-credit', $(this.elOverlay));
      this.elSponsor = $('.sponsor_content', $(this.elOverlay));

	  // allow overlay to handle click      
      $(this.elOverlay).click(function(evt){
      	window.location = $(this).attr('data-url'); 
      });
	  // don't let sponsor click get to overlay
      $('.sponsor_content', this.elOverlay).click(function(evt){
      	evt.stopPropagation();
	  });
      
      return this;    	
	},
    setZIndex: function(nZIndexBack, nZIndexFore){
      $(this.el).css("z-index", nZIndexBack);
      $(this.elOverlay).css("z-index", nZIndexFore);
	},
    load: function(){
      var self = this;
    	
      this.elHeroLeft.css('top', -(this.elHeroLeft.height() + OFFSCREEN_Y));
      this.elHeroLeftBtn = $('.hero_btn', this.elHeroLeft);
      this.elHeroRight.css('top', -(this.elHeroRight.height() + OFFSCREEN_Y));
      this.elCredit.css('top', ($('#home_header').height() + OFFSCREEN_Y));
      this.elSponsor.css('top', ($('#home_header').height() + OFFSCREEN_Y));

      var elImages = $('.image_container', $(this.el));
      var imgLoad = imagesLoaded(elImages);
	  imgLoad.on('always', function(instance) {
	    for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
	      if ($(imgLoad.images[i].img).hasClass('scale')) {
	  	    $(imgLoad.images[i].img).addClass('scale_image_ready');	      	
	      }
	   	}	  		
        // fire event
        app.dispatcher.trigger("HomeHeroView:ready", self);                        
	  });
    },
    show: function(nShowContentDelay){
      this.Active = true;
      var self = this;

	  $(this.el).css('visibility', 'visible');
	  $(this.elOverlay).css('visibility', 'visible');

	  // update pos
	  $('img.scale_image_ready', $(this.el)).imageScale();
	  // fade in - delay adding class to ensure image is ready  
	  $('.fade_on_load', $(this.el)).addClass('tb-fade-in-no-delay');
	  $('.image_container', $(this.el)).css('opacity', 1);
	  // force update to fix blurry bug
	  resrc.resrcAll();
		  
      this.elHeroLeft.css('visibility', 'visible');
      this.elHeroLeft.addClass('hero-move');

      this.elHeroLeftBtn.addClass('hero-move');
      
      this.elHeroRight.css('visibility', 'visible');
      this.elHeroRight.addClass('hero-move');
      
      this.elCredit.css('visibility', 'visible');
      this.elCredit.addClass('hero-move');

      this.elSponsor.css('visibility', 'visible');
      this.elSponsor.addClass('hero-move');
      
	  setTimeout(function() {
	  	if (self.Active) {
          self.elHeroLeft.css('top', 24);             
          self.elHeroLeftBtn.css('top', 24);
          self.elHeroRight.css('top', 46);      
          self.elCredit.css('top', $('#home_header').height() - self.elCredit.height() - 12);
          self.elSponsor.css('top', $('#home_header').height() - self.elSponsor.height() - 12);                                 
	  	}
  	  }, nShowContentDelay);      
    },	
    hide: function(){
      this.Active = false;
	  $('.image_container', $(this.el)).css('opacity', 0);
	  
      this.elHeroLeft.css('top', -(this.elHeroLeft.height() + OFFSCREEN_Y));             
      this.elHeroLeftBtn.css('top', 0);
      this.elHeroRight.css('top', -(this.elHeroRight.height() + OFFSCREEN_Y));                   
      this.elCredit.css('top', ($('#home_header').height() + OFFSCREEN_Y));
      this.elSponsor.css('top', ($('#home_header').height() + OFFSCREEN_Y));
	},
  });

  return HomeHeroView;
});
