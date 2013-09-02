var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';
//var RESTAPI_BASEURL = 'http://localhost:8888/api/';

define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'views/TrailMapView',
  'views/TrailAltitudeView'  
], function(_, Backbone, TrailModel, TrailMapView, TrailAltitudeView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    var trailModel = new TrailModel();

    this.trailMapView = new TrailMapView({ el: '#trailmapview', model: trailModel });
    this.trailAltitudeView = new TrailAltitudeView({ el: '#trailaltitudeview', model: trailModel });
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    function handleResize() {      
      $('.image').resizeToParent();
            
      self.trailMapView.update();
      self.trailAltitudeView.update();

      $('.image').show();
    }        
    
    // get trail    
//    trailModel.set('id', 6);    
    trailModel.set('id', 14);    
    console.log('Fetch ID:'+trailModel.get('id'));            
    trailModel.fetch({
      success: function () {
        console.log('Fetched');
        self.trailMapView.render();
        self.trailAltitudeView.render();
      }      
    });    
  };
    
  return { 
    initialize: initialize
  };   
});  
