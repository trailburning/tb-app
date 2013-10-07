define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailModel = Backbone.Model.extend({    
    defaults: function() {
      return {
        id: 0
      };
    },    
    urlRoot: RESTAPI_BASEURL + 'v1/route',
    initialize: function() {            
    }
  });
  return TrailModel;
  
});
