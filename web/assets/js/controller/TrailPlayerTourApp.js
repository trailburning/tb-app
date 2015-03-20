var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/TrailPlayerTourView'
], function(_, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
//    L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';
    L.mapbox.accessToken = 'pk.eyJ1IjoibW9yZ2FuaGVybG9ja2VyIiwiYSI6Ii1zLU4xOWMifQ.FubD68OEerk74AYCLduMZQ';

    this.appView = new AppView({ el: '#app-view' });
  };
    
  return { 
    initialize: initialize
  };   
});  
