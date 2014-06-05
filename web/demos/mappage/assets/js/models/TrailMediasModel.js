define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMediasModel = Backbone.Model.extend({    
    defaults: function() {
      return {
        id: 0
      };
    },    
    initialize: function() {            
    }
  });
  return TrailMediasModel;
  
});
