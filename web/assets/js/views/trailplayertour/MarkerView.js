define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  var MarkerView = Backbone.View.extend({
    initialize: function(){
    },
    getLat: function(){
      return this.options.lat;
    },
    getLong: function(){
      return this.options.long;
    },
    getTitle: function(){
      return this.options.title;
    }

  });

  return MarkerView;
});
