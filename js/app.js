var app = app || {};

//var RESTAPI_BASEURL = 'http://tb-restapi.herokuapp.com/';
var RESTAPI_BASEURL = 'http://localhost:8888/';

define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'views/TrailView'
], function(_, Backbone, TrailModel, TrailView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    var trailModel = new TrailModel();

    this.trailView = new TrailView({ el: '#trailview', model: trailModel });
    this.trailView.render();   
        
    // get trail    
//    trailModel.set('id', 8);
//    this.trailView.getTrail();
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();    
    
    function handleResize() {      
      var nWidth = ($(window).width() * 80) / 100;
      var nHeight = Math.round(nWidth / 1.333);  
      
      $('#bigContainer').height(nHeight);
      $('#sideContainer').height(nHeight);
      
      $('.image').resizeToParent();
      
      self.trailView.test();
    }    
  };
    
  return { 
    initialize: initialize
  };   
});  
