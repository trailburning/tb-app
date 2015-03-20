define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  var StoryItemView = Backbone.View.extend({
    initialize: function(){

    },
    show: function(){    
      $(this.el).css('opacity', 1);
      $(this.el).addClass('focus');
    },
    hide: function(){    
      $(this.el).css('opacity', 0);
      $(this.el).removeClass('focus');
    }
  });

  return StoryItemView;
});
