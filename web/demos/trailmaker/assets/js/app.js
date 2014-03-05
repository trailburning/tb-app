var app = app || {};

var RESTAPI_BASEURL = 'http://beta.trailburning.com/api/';
//var RESTAPI_BASEURL = 'http://trailburning-staging.herokuapp.com/';
//var RESTAPI_BASEURL = 'http://localhost:8888/trailburning_api/';

var nTrail = 154;

define([
  'underscore', 
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/AppView'  
], function(_, Modernizr, Backbone, TrailModel, AppView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.trailModel = new TrailModel();
            
//    this.trailModel.id = 164;
//    this.trailModel.destroy();
            
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
