var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/TrailView'
], function(_, Modernizr, Backbone, TrailModel, AppView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
    var self = this;
                
    this.trailModel = new TrailModel();

    this.appView = new AppView({ el: '#appview', model: this.trailModel, nTrail: TB_TRAIL_ID });
            
    var imgLoad = imagesLoaded('.panels .scale');
    imgLoad.on('always', function(instance) {
      for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
        $(imgLoad.images[i].img).addClass('scale_image_ready');
        // update pos
        $(imgLoad.images[i].img).imageScale();
      }
      // fade in - delay adding class to ensure image is ready  
      $('.panels .fade_on_load').addClass('tb-fade-in');
      $('.panels .image_container').css('opacity', 1);
    });    
  };
    
  return { 
    initialize: initialize
  };   
});  
