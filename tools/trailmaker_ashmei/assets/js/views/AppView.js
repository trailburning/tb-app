define([
  'underscore', 
  'backbone',
  'views/TrailMapView',
  'views/StepWelcomeView',  
  'views/Step1View',  
  'views/Step2View',
  'views/Step3View'
], function(_, Backbone, TrailMapView, StepWelcomeView, Step1View, Step2View, Step3View){

  var AppView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("StepWelcomeView:submitclick", this.onStepWelcomeViewSubmitClick, this);
      app.dispatcher.on("Step1View:submitclick", this.onStep1ViewSubmitClick, this);
      app.dispatcher.on("Step2View:gpxuploaded", this.onStep2ViewGPXUploaded, this);
      app.dispatcher.on("Step2View:submitclick", this.onStep2ViewSubmitClick, this);
      app.dispatcher.on("Step3View:submitclick", this.onStep3ViewSubmitClick, this);

      // mla test
      this.model.set('name', 'Trailburning');
      this.model.set('email', 'events@trailburning.com');
      this.model.set('event_name', 'Thames Festival of Running');
      this.model.set('trail_name', 'Thames Trail Marathon');
      this.setTitles();

      // Trail Map    
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
      
      // Step Welcome
      this.stepWelcomeView = new StepWelcomeView({ el: '#step_welcome_view', model: this.model });
//      $('#step_welcome_view').show();
//      this.stepWelcomeView.render();    
      // Step 1
      this.step1View = new Step1View({ el: '#step1_view', model: this.model });
//      $('#step1_view').show();
//      this.step1View.render();    
      // Step 2
      this.step2View = new Step2View({ el: '#step2_view', model: this.model });
      $('#step2_view').show();    
      this.step2View.render();
      // Step 3
      this.step3View = new Step3View({ el: '#step3_view', model: this.model });
//      $('#step3_view').show();    
//      this.step3View.render();
  
      $('#footerview').show();            
    },
    setTitles: function(){
      // set title
      if (this.model.get('event_name') != '' && this.model.get('event_name') != undefined) {
        $('#trail_info').show();
        $('#trail_info .event_name').html(this.model.get('event_name'));
        $('#trail_info .event_name').css('visibility', 'visible');
      }
      if (this.model.get('trail_name') != '' && this.model.get('trail_name') != undefined) {
        $('#trail_info').show();
        $('#trail_info .trail_name').html(this.model.get('trail_name'));
        $('#trail_info .trail_name').css('visibility', 'visible');
      }
    },
    getTrail: function(){
      var self = this;
      
      this.model.fetch({
        success: function () {
          self.getTimeZone();
        }      
      });        
    },
    getTimeZone: function(){
      var self = this;    
      
      var data = this.model.get('value');      
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
          self.trailMapView.setTimeZoneData(self.timezoneData);
          self.trailMapView.render();          
        },
      });
    },
    onStepWelcomeViewSubmitClick: function(stepWelcomeView){
      $('#step_welcome_view').hide();
      $('#step1_view').show();
      this.step1View.render();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    },
    onStep1ViewSubmitClick: function(step1View){
      $('#step1_view').hide();
      $('#step2_view').show();
      this.step2View.render();
      $('#trail_map_overlay').show();
            
      this.setTitles();           
            
      this.trailMapView.render();          
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    },   
    onStep2ViewGPXUploaded: function(step2View){
      this.getTrail();      
      
      $('#trail_map_overlay', $(this.el)).hide();
      $('#view_map_btns', $(this.el)).show();
    },    
    onStep2ViewSubmitClick: function(step2View){      
      var jsonObj = {'id':this.model.get('id'), 'name':this.model.get('name'), 'email':this.model.get('email'), 'event_name':this.model.get('event_name'), 'trail_name':this.model.get('trail_name'), 'trail_notes':this.model.get('trail_notes'), 'media':this.trailMapView.collectionMedia.toJSON()};
      var postData = JSON.stringify(jsonObj);
      var postArray = {json:postData};
      
//      console.log(postData);      
      $.ajax({
        type: "POST",
        dataType: "json",
        url: 'server/sendTrailProxy.php',
        data: postArray,
        error: function(data) {
          console.log('error:'+data.responseText);      
          console.log(data);      
        },
        success: function(data) {      
          console.log('success');
          console.log(data);
        }
      });  

      $('#step2_view').hide();    
      $('#step3_view').show();    
      this.step3View.render();
      $('#trail_map_overlay').show();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    }
    
  });

  return AppView;
});
