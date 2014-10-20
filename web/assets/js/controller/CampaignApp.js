var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/CampaignView',
  'views/SearchView'
], function(_, Modernizr, Backbone, AppView, SearchView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;

	L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    this.appView = new AppView({ el: '#appview' });
            
	this.searchView = new SearchView({ el: '#searchview' });
    
  	var imgLoad = imagesLoaded('.scale');
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

