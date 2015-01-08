var app = app || {};

var nTrail = 0;

define([
  'underscore', 
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/TrailmakerView',
], function(_, Modernizr, Backbone, TrailModel, AppView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.trailModel = new TrailModel();
                        
    $('#search_field').focus(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });
    $('#search_form').submit(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });    

    this.appView = new AppView({ el: '#appview', model: this.trailModel });
  };
    
  return { 
    initialize: initialize
  };   
});  