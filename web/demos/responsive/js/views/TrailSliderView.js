define([
  'underscore', 
  'backbone'
], function(_, Backbone){
    
  var TrailSliderView = Backbone.View.extend({
    initialize: function(){
      var self = this;

	  this.mediaCollection = this.options.mediaCollection;
	  this.mediaModel = this.options.mediaModel;
	  this.slider = null;
	  
	  $('.royalSlider').show();
    },
    render: function(){
      var self = this;
      
      var strImage;
 	  this.mediaCollection.each(function(model) {
// 	    console.log(model);
// 	    console.log(model.get('versions')[0].path);
 	    
 	    var nWidth = 768;
 	    if (Modernizr.mq('only all and (min-width: 768px)')) {
 	      nWidth = 992;
 	    }
 	    if (Modernizr.mq('only all and (min-width: 992px)')) {
 	      nWidth = 1024;
 	    }
 	    if (Modernizr.mq('only all and (min-width: 1200px)')) {
 	      nWidth = 1400;
 	    }
 	    
// 	    strImage = 'http://tbmedia.imgix.net//media.trailburning.com'+model.get('versions')[0].path+'?fm=jpg&q=80&w=1280&fit=fill';
 	    strImage = 'http://tbmedia.imgix.net//media.trailburning.com'+model.get('versions')[0].path+'?fm=jpg&q=80&w='+nWidth+'&fit=fill';
	  	if (Number(model.get('tags').height) > Number(model.get('tags').width)) {
	  	  // fix width and height and add background
//	  	  strImage = 'http://tbmedia.imgix.net//media.trailburning.com'+model.get('versions')[0].path+'?fm=jpg&q=80&w=1280&h=960&fit=fill&bg=000000';
 	    }
	    $(self.el).append('<div class="rsImg">'+strImage+'</div>');
	  });    	
    		  
      var strTransition = 'slide';
	  if (!Modernizr.touch) {
	  	strTransition = 'fade';
	  }
	  	  
  	  $(".royalSlider").royalSlider({
  	  	imageScaleMode: 'fit-if-smaller',
  	  	controlNavigation: 'none',
  	  	slidesSpacing: 0,
  	  	loop: true,
  	  	transitionType: strTransition,
        keyboardNavEnabled: true,
        autoScaleSlider: false,
    	fullscreen: {
    	  enabled: true,
    	  nativeFS: false
    	}
      });  	
      
	  this.slider = $(".royalSlider").data('royalSlider');

	  this.slider.ev.on('rsBeforeAnimStart', function(event) {
	    app.dispatcher.trigger("TrailSliderView:slidechanged", self.slider.currSlide.id);
	  });	  
	     
      // fire event
      app.dispatcher.trigger("TrailSliderView:slidechanged", this.slider.currSlide.id);                
	},
	gotoMedia: function(nMedia){
	  this.slider.goTo(nMedia);
    }
    
  });

  return TrailSliderView;
});
