define([
  'underscore', 
  'backbone'
], function(_, Backbone){
    
  var TrailSliderView = Backbone.View.extend({
    initialize: function(){
      var self = this;
      
	  this.bRendered = false;
	  this.mediaCollection = this.options.mediaCollection;
	  this.mediaModel = this.options.mediaModel;
	  this.slider = null;
	  
	  $('.royalSlider').show();
    },
    addSlide: function(model){
 	  var nWidth = 768;
 	  var nHeight = 576;
 	  if (Modernizr.mq('only all and (min-width: 768px)')) {
 	    nWidth = 992;
 	    nHeight = 744;
 	  }
 	  if (Modernizr.mq('only all and (min-width: 992px)')) {
 	    nWidth = 1024;
 	    nHeight = 768;
 	  }
 	  if (Modernizr.mq('only all and (min-width: 1200px)')) {
 	    nWidth = 1400;
 	    nHeight = 1050;
 	  }
 	    
 	  strImage = 'http://tbmedia.imgix.net//media.trailburning.com'+model.get('versions')[0].path+'?fm=jpg&q=80&w='+nWidth+'&fit=fill';
	  if (Number(model.get('tags').height) > Number(model.get('tags').width)) {
	  	// fix width and height and add background
//	  	strImage = 'http://tbmedia.imgix.net//media.trailburning.com'+model.get('versions')[0].path+'?fm=jpg&q=80&w='+nWidth+'&h='+nHeight+'&fit=fill&bg=000000';
 	  }
 	    
 	  var strHTML = '<div class="rsImg">'+strImage+'</div>';
	  $(this.el).append(strHTML);
    },
    render: function(){
      var self = this;
      
      // already rendered?  Just update
      if (this.bRendered) {
      	this.slider.updateSliderSize(true);
        return;         
      }        
      
      var strImage;
 	  this.mediaCollection.each(function(model) {
 	  	self.addSlide(model); 	  	
	  });    	
    		  
      var strTransition = 'slide';
	  if (!Modernizr.touch) {
	  	strTransition = 'fade';
	  }
	  	  
  	  $(".royalSlider").royalSlider({
//  	  	imageScaleMode: 'fit-if-smaller',
  	  	imageScaleMode: 'fill',
  	  	controlNavigation: 'none',
  	  	slidesSpacing: 0,
  	  	loop: true,
  	  	transitionType: strTransition,
        keyboardNavEnabled: true,
        autoScaleSlider: false,
    	fullscreen: {
    	  enabled: true,
    	  nativeFS: false
    	},
    	block: {
    		// animated blocks options go gere
    	}    	
      });  	
      
	  this.slider = $(".royalSlider").data('royalSlider');

	  this.slider.ev.on('rsBeforeAnimStart', function(event) {
	    app.dispatcher.trigger("TrailSliderView:slidechanged", self.slider.currSlide.id);
	  });	  
	     
      // fire event
      app.dispatcher.trigger("TrailSliderView:slidechanged", this.slider.currSlide.id);
      
      this.bRendered = true;                
	},
	gotoMedia: function(nMedia){
	  this.slider.goTo(nMedia);
    }
    
  });

  return TrailSliderView;
});
