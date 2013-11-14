var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';

define([
  'underscore', 
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/TrailMapView'
], function(_, Modernizr, Backbone, TrailModel, TrailMapView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    this.trailModel = new TrailModel();
    this.timezoneData = null;
            
    $('#search_field').focus(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });
    $('#search_form').submit(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });    
    
    function getTimeZone() {      
      var data = self.trailModel.get('value');      
      var firstPoint = data.route.route_points[0];
      var nTimestamp = firstPoint.tags.datetime;
      
      var strURL = 'https://maps.googleapis.com/maps/api/timezone/json?location='+Number(firstPoint.coords[1])+','+Number(firstPoint.coords[0])+'&timestamp='+nTimestamp+'&sensor=false'; 
      
      $.ajax({
        url: strURL,
        type: 'GET',            
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
          self.timezoneData = data; 
          startup();
        },
      });
    }    
    
    function startup() {      
      self.trailMapView = new TrailMapView({ el: '#trail_map_view', model: self.trailModel, timezoneData: self.timezoneData });
      self.trailMapView.render();          
    }
    
    var self = this;    
    var nTrail = 33;
    
    // get trail    
    this.trailModel.set('id', nTrail);             
    this.trailModel.fetch({
      success: function () {
        getTimeZone();
      }      
    });
    
  };
    
  return { 
    initialize: initialize
  };   
});  
