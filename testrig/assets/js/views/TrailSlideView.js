define([
  'underscore', 
  'backbone'
], function(_, Backbone){
  
  var TrailSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlideViewTemplate').text());        
    },            
    render: function(){
    }
  });

  return TrailSlideView;
});
