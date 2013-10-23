var app = app || {};

define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    var imgLoad = imagesLoaded('.scale');
    imgLoad.on('always', function(instance) {
      for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
        $(imgLoad.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $("img.scale_image_ready").imageScale();
      // fade in
      $('.image_container').css('opacity', 1);
    });
    
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
  };
    
  return { 
    initialize: initialize
  };   
});  
