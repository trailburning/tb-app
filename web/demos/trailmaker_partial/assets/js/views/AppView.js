define([
  'underscore', 
  'backbone',
  'models/TrailMediasModel',    
  'views/TrailMapView',
  'views/StepWelcomeView',  
  'views/StepDetailView',  
  'views/StepRouteView',
  'views/StepRouteEditView',
  'views/StepPublishedView'
], function(_, Backbone, TrailMediasModel, TrailMapView, StepWelcomeView, StepDetailView, StepRouteView, StepRouteEditView, StepPublishedView){

  var AppView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("StepWelcomeView:submitclick", this.onStepWelcomeViewSubmitClick, this);
      app.dispatcher.on("StepDetailView:submitclick", this.onStepDetailViewSubmitClick, this);
      app.dispatcher.on("StepRouteView:gpxuploaded", this.onStepRouteViewGPXUploaded, this);
      app.dispatcher.on("StepRouteEditView:photouploaded", this.onStepRouteEditViewPhotoUploaded, this);
      app.dispatcher.on("StepRouteEditView:galleryPhotoClick", this.onStepRouteEditViewGalleryPhotoClick, this);
      app.dispatcher.on("StepRouteEditView:submitclick", this.onStepRouteEditViewSubmitClick, this);
      app.dispatcher.on("StepPublishedView:submitclick", this.onStepPublishedViewSubmitClick, this);

      this.mediasModel = new TrailMediasModel();
      this.mediaCollection = new Backbone.Collection();

      $('#trail_map_view').addClass('map_large');

      var self = this;
      $(window).resize(function() {
        self.handleResize();
      });    

      // Trail Map    
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
      
      // Step Welcome
      this.stepWelcomeView = new StepWelcomeView({ el: '#step_welcome_view', model: this.model });
//      $('#step_welcome_view').show();
//      this.stepWelcomeView.render();    
      // Step Detail
      this.stepDetailView = new StepDetailView({ el: '#step_detail_view', model: this.model });
//      $('#step_detail_view').show();
//      this.stepDetailView.render();    
      // Step Rpute
      this.stepRouteView = new StepRouteView({ el: '#step_route_view', model: this.model });
      $('#step_route_view').show();    
      this.stepRouteView.render();
      // Step Route Edit
      this.stepRouteEditView = new StepRouteEditView({ el: '#step_route_edit_view', model: this.model, mediaCollection: this.mediaCollection });
      // Step Published
      this.stepPublishedView = new StepPublishedView({ el: '#step_published_view', model: this.model });
//      $('#step_published_view').show();    
//      this.stepPublishedView.render();
    
  	  this.handleResize();
//      this.trailMapView.render();
    
      $('#footerview').show();            
    },
    handleResize: function(){
      var elContentView = $('#contentview');
      var elHeaderView = $('#headerview');
      var elFooterView = $('#headerview');
      var nHeight = 0;
	  
	  if ($('#trail_map_view.map_large').length) {
	  	nHeight = $(window).height() - elHeaderView.height();
		if (nHeight < $('#steps').height()) {
		  nHeight = $('#steps').height();
		}			  	
	  	$('#contentview').height(nHeight);
	  }
	  else {
	    $('#contentview').height('100%');
	  }
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
	      self.stepRouteEditView.renderSlideshow();
        }
      });
    },
    onStepWelcomeViewSubmitClick: function(stepWelcomeView){
      $('#step_welcome_view').hide();
      $('#step_detail_view').show();
      this.stepDetailView.render();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    },
    onStepDetailViewSubmitClick: function(stepDetailView){
      $('#step_detail_view').hide();
      $('#step_route_view').show();
      this.stepRouteView.render();
      $('#trail_map_overlay').show();
            
      this.setTitles();           
            
      this.trailMapView.render();          
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    },   
    onStepRouteViewGPXUploaded: function(step2View){
	  $('#trail_map_view').removeClass('map_large');
	  $('#trail_map_view').addClass('map_small');
  
      $('#step_route_view').hide();
      $('#step_route_edit_view').show();
  	  this.stepRouteEditView.render();
  
      this.handleResize();
    	
      // mla test
      this.model.set('id', 148);
      this.getTrail();      
      
      $('#trail_map_overlay', $(this.el)).hide();
      $('#view_map_btns', $(this.el)).show();
    },    
    onStepRouteEditViewPhotoUploaded: function(trailUploadPhotoView){
	  var data = trailUploadPhotoView.photoData.value[0];
	  this.trailMapView.addMarker(data, true, "");
	  this.mediaCollection.add(data);
	  
	  this.stepRouteEditView.renderSlideshow();
    },    
    onStepRouteEditViewGalleryPhotoClick: function(trailGallerySlideView){
      this.trailMapView.selectMarker(trailGallerySlideView.model.id);    
	},    
    onStepRouteEditViewSubmitClick: function(step2View){      
//      $('#content_overlay').show();
/*      
      return;
    	
      var jsonObj = {'name':'trailburning', 'region':'Berlin', 'about':'A really lovely trail.', 'publish':true};
      var postData = JSON.stringify(jsonObj);
      var postArray = {json:postData};

      var strURL = RESTAPI_BASEURL + 'v1/route/' + this.model.id;      
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
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
*/        
/*      
name (string)
region (string)
about (string)
publish (boolean)
route_type_id (integer)
route_category_id (integer)      
*/

/*    	
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

      $('#step_route_view').hide();    
      $('#step_published_view').show();    
      this.stepPublishedView.render();
      $('#trail_map_overlay').show();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
*/          

	  $('#trail_map_view').removeClass('map_small');
	  $('#trail_map_view').addClass('map_large');

      $('#step_route_edit_view').hide();    
      $('#step_published_view').show();    
      this.stepPublishedView.render();
      
      $('#trail_map_overlay').show();
      $('#view_map_btns', $(this.el)).hide();

      this.handleResize();
      this.trailMapView.render();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    }
  });

  return AppView;
});
