var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';

define([
  'underscore', 
  'modernizr',
  'backbone',
  'models/TrailModel',
  'views/StepWelcomeView',  
  'views/Step1View',  
  'views/Step2View'  
], function(_, Modernizr, Backbone, TrailModel, StepWelcomeView, Step1View, Step2View){
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

    var self = this;
    
    app.dispatcher.on("StepWelcomeView:submitclick", onStepWelcomeViewSubmitClick, this);
    app.dispatcher.on("Step1View:submitclick", onStep1ViewSubmitClick, this);
    
    // Step Welcome
    this.stepWelcomeView = new StepWelcomeView({ el: '#step_welcome_view', model: self.trailModel });
//    $('#step_welcome_view').show();
//    this.stepWelcomeView.render();    
    // Step 1
    this.step1View = new Step1View({ el: '#step1_view', model: self.trailModel });
    $('#step1_view').show();
    this.step1View.render();    
    // Step 2
    this.step2View = new Step2View({ el: '#step2_view', model: self.trailModel });
//    $('#step2_view').show();    
//    $('#step2_view').height(800);    
//    this.step2View.render();

    $('#footerview').show();      
    
    function onStepWelcomeViewSubmitClick(stepWelcomeView){
      $('#step_welcome_view').hide();
      $('#step1_view').show();
      this.step1View.render();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    }
    
    function onStep1ViewSubmitClick(step1View){
      $('#step2_view').height($('#step1_view').height());
      $('#step1_view').hide();
      $('#step2_view').show();
      this.step2View.render();
      $('#step2_view .trail_map_overlay').show();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    }    
    
  };
    
  return { 
    initialize: initialize
  };   
});  
