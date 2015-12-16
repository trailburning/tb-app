var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/JourneyTourView'
], function(_, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.appView = new AppView({ el: '#app-view' });
  };
    
  return { 
    initialize: initialize
  };   
});  
