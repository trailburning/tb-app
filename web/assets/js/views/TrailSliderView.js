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
	  
	  $(this.el).show();
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
 	    
 	  strImage = 'http://tbmedia2.imgix.net/'+model.get('versions')[0].path+'?fm=jpg&q=80&w='+nWidth+'&fit=fill';
	  if (Number(model.get('tags').height) > Number(model.get('tags').width)) {
	  	// fix width and height and add background
	  	strImage = 'http://tbmedia2.imgix.net/'+model.get('versions')[0].path+'?fm=jpg&q=80&w='+nWidth+'&h='+nHeight+'&fit=fill&bg=000000';
 	  }
 	    
 	  var strHTML = '<div class="rsImg">'+strImage+'</div>';
	  $(this.el).append(strHTML);
    },
    gotoSlide: function(nSlide){
      this.slider.goTo(nSlide);
    },
    toggleFullscreen: function(){
      if (this.slider.isFullscreen) {
        this.slider.exitFullscreen();	
      }
      else {
        this.slider.enterFullscreen();	
      }
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
	  	  
  	  this.slider = $(this.el).royalSlider({
  	  	imageScaleMode: 'fill',
  	  	controlNavigation: 'none',
  	  	slidesSpacing: 0,
  	  	loop: true,
  	  	transitionType: strTransition,
        keyboardNavEnabled: true,
        autoScaleSlider: false,
	    autoPlay: {
    	  enabled: true,
    	  pauseOnHover: false,
    	  stopAtAction: false,
    	  delay: self.options.nHoldSlide
    	},        
    	fullscreen: {
    	  enabled: true,
    	  nativeFS: false
    	}
      }).data('royalSlider');

      $(this.el).append('<div class="toggleBtn"><div class="toggleIcn photo"></div></div>');
	  $('.rsContainer', this.el).append('<div class="trail_grad_back"></div>');
     
	  this.slider.ev.on('rsBeforeMove', function(event, type, userAction ) {
	  	// fire event
	    app.dispatcher.trigger("TrailSliderView:slidemoving", type);	  		
	  });
	  	  
	  this.slider.ev.on('rsEnterFullscreen', function() {
	  	
	  	$('#trail_stats_view').addClass('fullscreen');
	  	
	  	$('#trail_author_view').hide();
	  	$('#trail_fullscreen_author_view').show();
	  	$('.toggleBtn', this.el).hide();
	  });
	  
	  this.slider.ev.on('rsExitFullscreen', function() {
	  	$('#trail_stats_view').removeClass('fullscreen');
	  	
	  	$('#trail_fullscreen_author_view').hide();
	  	$('#trail_author_view').show();
	  	$('.toggleBtn', this.el).show();
	  });
	  	  
      // fire event
      app.dispatcher.trigger("TrailSliderView:ready");
      
      this.bRendered = true;                
	},
	gotoMedia: function(nMedia){
	  this.slider.goTo(nMedia);
    },
	play: function(){
	  this.slider.startAutoPlay();
    },
	stop: function(){
	  this.slider.stopAutoPlay();
    }
    
  });

  return TrailSliderView;
});
