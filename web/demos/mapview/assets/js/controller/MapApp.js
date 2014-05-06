var app = app || {};

define([
  'underscore', 
  'modernizr',
  'backbone',
  'views/MapView'  
], function(_, Modernizr, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
    
  var initialize = function() {
    var self = this;

    this.appView = new AppView({ el: '#mapview' });
  };
    
  return { 
    initialize: initialize
  };   
});  
