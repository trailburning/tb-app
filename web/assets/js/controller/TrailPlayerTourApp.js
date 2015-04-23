var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/TrailPlayerTourView'
], function(_, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    L.mapbox.accessToken = 'pk.eyJ1IjoibWFsbGJldXJ5IiwiYSI6IjJfV1MzaE0ifQ.scrjDE31p7wBx7-GemqV3A';

    this.appView = new AppView({ el: '#app-view' });
  };
    
  return { 
    initialize: initialize
  };   
});  
