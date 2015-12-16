define([
  'underscore', 
  'backbone'
], function(_, Backbone, JourneyView){
  var AppView = Backbone.View.extend({
    initialize: function(){
      var el = $('.scale');
      $(el).imagesLoaded()
        .progress( function(instance, image) {
          $(image.img).addClass('scale-image-ready');
          // update pos
          $(image.img).imageScale({rescaleOnResize: true});          
          var elContainer = $(image.img).parent();
          if (elContainer.hasClass('fade-on-load')) {
              // fade in - delay adding class to ensure image is ready  
            elContainer.addClass('fade-in');
            elContainer.css('opacity', 1);
          }
      });
    }

  });

  return AppView;
});
