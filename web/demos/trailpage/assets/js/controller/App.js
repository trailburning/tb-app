var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/TrailPageView'  
], function(_, Backbone, AppView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;

    this.appView = new AppView({ el: '#appview' });
  };
    
  return { 
    initialize: initialize
  };   
});  

