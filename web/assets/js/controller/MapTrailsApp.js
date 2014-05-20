var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/MapView'
], function(_, Modernizr, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.appView = new AppView({ el: '#appview' });    
  };
    
  return { 
    initialize: initialize
  };   
});  
