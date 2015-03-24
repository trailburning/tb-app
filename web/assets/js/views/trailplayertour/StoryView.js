define([
  'underscore', 
  'backbone',
  'views/trailplayertour/StoryItemView'
], function(_, Backbone, StoryItemView){
  var StoryView = Backbone.View.extend({
    initialize: function(){

      this.arrItems = [];
    },
    render: function(){
      var el = $('.story-item', this.el);
      for (var nItem=0; nItem < el.length; nItem++) {
        this.arrItems.push(new StoryItemView({ el: el[nItem] }));
      }

      $('.scale').imagesLoaded()
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
    },
    show: function(nItem){
      var storyItemView = this.arrItems[nItem];
      storyItemView.show();
    },
    hide: function(nItem){
      var storyItemView = this.arrItems[nItem];
      storyItemView.hide();
    }    

  });

  return StoryView;
});
