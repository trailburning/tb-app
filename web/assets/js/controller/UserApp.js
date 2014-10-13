var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/UserView',
  'views/SearchView'  
], function(_, Modernizr, Backbone, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {        
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();
    
    this.appView = new AppView({ el: '#appview' });
    
	this.searchView = new SearchView({ el: '#searchview' });
    
    var imgLoad = imagesLoaded('.scale');
    imgLoad.on('always', function(instance) {
      for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
        $(imgLoad.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $("img.scale_image_ready").imageScale();
      // fade in - delay adding class to ensure image is ready  
      $('.fade_on_load').addClass('tb-fade-in');
      $('.image_container').css('opacity', 1);
    });
    
  	$('#footerview').show();  	
  	    
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }    
  };
    
  return { 
    initialize: initialize
  };   
});  
