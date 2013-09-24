define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailMediaModel = Backbone.Model.extend({    
    defaults: function() {
      return {
        id: 0
      };
    },    
    initialize: function() {            
    }
  });
  return TrailMediaModel;
  
});
