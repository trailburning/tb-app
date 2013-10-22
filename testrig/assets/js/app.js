var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/TrailSlideView'  
], function(_, Backbone, TrailSlideView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    var imgLoad = imagesLoaded('#container');
    imgLoad.on( 'always', function(instance) {
      console.log( imgLoad.images.length + ' images loaded' );
      
      $('#container').css('opacity', 1);
      $('#container img').css('opacity', 1);
    });

    this.trailSlideView = new TrailSlideView({ el: '#trail_slide_view', model: this.mediaModel });
  };
    
  return { 
    initialize: initialize
  };   
});  
