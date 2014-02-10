define([
  'underscore', 
  'backbone',
  'models/TrailMediasModel',    
  'views/TrailMapView',
  'views/StepWelcomeView',  
  'views/Step1View',  
  'views/Step2View',
  'views/Step3View'
], function(_, Backbone, TrailMediasModel, TrailMapView, StepWelcomeView, Step1View, Step2View, Step3View){

  var AppView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("StepWelcomeView:submitclick", this.onStepWelcomeViewSubmitClick, this);
      app.dispatcher.on("Step1View:submitclick", this.onStep1ViewSubmitClick, this);
      app.dispatcher.on("Step2View:gpxuploaded", this.onStep2ViewGPXUploaded, this);
      app.dispatcher.on("Step2View:photouploaded", this.onStep2ViewPhotoUploaded, this);
      app.dispatcher.on("Step2View:galleryPhotoClick", this.onStep2ViewGalleryPhotoClick, this);
      app.dispatcher.on("Step2View:submitclick", this.onStep2ViewSubmitClick, this);
      app.dispatcher.on("Step3View:submitclick", this.onStep3ViewSubmitClick, this);

      this.mediasModel = new TrailMediasModel();
      this.mediaCollection = new Backbone.Collection();

      $('#trail_map_view').addClass('map_large');

      var self = this;
      $(window).resize(function() {
        self.handleResize();
      });    
  	  this.handleResize();

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
      this.step2View = new Step2View({ el: '#step2_view', model: this.model, mediaCollection: this.mediaCollection });
      $('#step2_view').show();    
      
      this.trailMapView.render();
      
      this.step2View.render();
      // Step 3
      this.step3View = new Step3View({ el: '#step3_view', model: this.model });
//      $('#step3_view').show();    
//      this.step3View.render();
    
      $('#footerview').show();            
    },
    handleResize: function(){
      var elContentView = $('#contentview');
      var elHeaderView = $('#headerview');
      var elFooterView = $('#headerview');
      var nContentY = elContentView.position().top;
	  
	  if ($('#trail_map_view.map_large').length) {
	  	console.log('LARGE');	  	
	  	$('#contentview').height($(window).height() - elHeaderView.height());
	  }
	  else {
	  	console.log('SMALL');	  	
	    $('#contentview').height('100%');
	  }
//	  $('#trail_map_view.map_large').height($(window).height() - elHeaderView.height() - elFooterView.height());
//	  $('#trail_map_view.map_large').height($(window).height() - elHeaderView.height());
//	  $('#contentview').height($('#trail_map_view').height());
	  
	  console.log('t:'+elHeaderView.height()+' : '+$(window).height()+' : '+$('#trail_map_view').height());
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
          self.trailMapView.render();          
          self.getTrailMedia();          
        }      
      });        
    },
    getTrailMedia: function(){
      var self = this; 
      
      this.mediasModel.url = RESTAPI_BASEURL + 'v1/route/'+this.model.get('id')+'/medias';
      this.mediasModel.fetch({
        success: function () {
	      var data = self.mediasModel.get('value');
	      $.each(data, function(key, jsonMedia) {
			self.trailMapView.addMarker(jsonMedia, true);
		    self.mediaCollection.add(jsonMedia);
	      });
	      self.step2View.renderSlideshow();
        }
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
	  $('#trail_map_view').removeClass('map_large');
	  $('#trail_map_view').addClass('map_small');
  
      this.handleResize();
    	
      // mla test
      this.model.set('id', 148);
    	
//      this.model.set('id', 148);
//      $('#step2_view .panel_container').hide();      
//      $('.map_step_container', $(this.el)).show();  
      // fire event
//      app.dispatcher.trigger("Step2View:gpxuploaded", self);                        
    	
    	
      this.getTrail();      
      
      $('#trail_map_overlay', $(this.el)).hide();
      $('#view_map_btns', $(this.el)).show();
    },    
    onStep2ViewPhotoUploaded: function(trailUploadPhotoView){
	  var data = trailUploadPhotoView.photoData.value[0];
	  this.trailMapView.addMarker(data, true, "");
	  this.mediaCollection.add(data);
	  
	  this.step2View.renderSlideshow();
    },    
    onStep2ViewGalleryPhotoClick: function(trailGallerySlideView){
      this.trailMapView.selectMarker(trailGallerySlideView.model.id);    
	},    
    onStep2ViewSubmitClick: function(step2View){      
      var jsonObj = {'id':this.model.get('id'), 'name':this.model.get('name'), 'email':this.model.get('email'), 'event_name':this.model.get('event_name'), 'trail_name':this.model.get('trail_name'), 'trail_notes':this.model.get('trail_notes'), 'media':this.mediasModel.get('value')};
      var postData = JSON.stringify(jsonObj);
      var postArray = {json:postData};
      
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
