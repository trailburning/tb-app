var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/TrailSlideView'  
], function(_, Backbone, TrailSlideView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    var imgLoad = imagesLoaded('.image_container');
    imgLoad.on('always', function(instance) {
//      console.log( imgLoad.images.length + ' images loaded' );      
      for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
//        console.log(imgLoad.images[i].img.src);        
        $(imgLoad.images[i].img).addClass('image_ready');
      }
      
      // update pos
      $(".imgLiquidFill").imgLiquid({
          fill: true,
          horizontalAlign: "center",
          verticalAlign: "center"
      });
    });

    this.trailSlideView = new TrailSlideView({ el: '#trail_slide_view', model: this.mediaModel });
    
    function handleResize() {
      $(".imgLiquidFill").imgLiquid({
          fill: true,
          horizontalAlign: "center",
          verticalAlign: "center"
      });
    }
    
  };
    
  return { 
    initialize: initialize
  };   
});  
