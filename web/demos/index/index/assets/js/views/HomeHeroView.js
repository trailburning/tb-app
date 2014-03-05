define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var OFFSCREEN_Y = 500;
  var SHOW_CONTENT_DELAY = 500;

  var HomeHeroView = Backbone.View.extend({
    initialize: function(){
      this.nPos = this.options.pos;
    },   
    render: function(){
      var self = this;
      
      this.elHeroLeft = $('.left', $(this.el));
      this.elHeroRight = $('.right', $(this.el));
      this.elCredit = $('.tb-credit', $(this.el));
      this.elSponsor = $('.sponsor_content', $(this.el));
      
      return this;    	
	},
    setZIndex: function(nZIndex){
      $(this.el).css("z-index", nZIndex);
	},
    load: function(){
      var self = this;
    	
      this.elHeroLeft.css('top', -(this.elHeroLeft.height() + OFFSCREEN_Y));
      this.elHeroLeftBtn = $('.hero_btn', this.elHeroLeft);
      this.elHeroRight.css('top', -(this.elHeroRight.height() + OFFSCREEN_Y));
      this.elCredit.css('top', ($('#home_header').height() + OFFSCREEN_Y));
      this.elSponsor.css('top', ($('#home_header').height() + OFFSCREEN_Y));
    	
      var elScale = $('.scale', $(this.el));
      var imgLoad = imagesLoaded(elScale);
	  imgLoad.on('always', function(instance) {
	    for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
	  	  $(imgLoad.images[i].img).addClass('scale_image_ready');
	   	}	  		
        // fire event
        app.dispatcher.trigger("HomeHeroView:ready", self);                        
	  });
    },
    show: function(){
      var self = this;

	  $(this.el).css('visibility', 'visible');
      
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
        self.elHeroLeft.css('top', 24);             
        self.elHeroLeftBtn.css('top', 24);
        self.elHeroRight.css('top', 46);      
        self.elCredit.css('top', $('#home_header').height() - self.elCredit.height() - 12);
        self.elSponsor.css('top', $('#home_header').height() - self.elSponsor.height() - 12);                                 
  	  }, SHOW_CONTENT_DELAY);      
    },	
    hide: function(){
	  $('.image_container', $(this.el)).css('opacity', 0);
	},
	hideContent: function(){
      this.elHeroLeft.css('top', -(this.elHeroLeft.height() + OFFSCREEN_Y));             
      this.elHeroLeftBtn.css('top', 0);
      this.elHeroRight.css('top', -(this.elHeroRight.height() + OFFSCREEN_Y));                   
      this.elCredit.css('top', ($('#home_header').height() + OFFSCREEN_Y));
      this.elSponsor.css('top', ($('#home_header').height() + OFFSCREEN_Y));
	}

  });

  return HomeHeroView;
});
