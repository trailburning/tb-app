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

  var TITLE_TIMER = 10000;

  var TITLE_OFF = 0;
  var TITLE_ON = 1;

  var AppView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("StepWelcomeView:submitclick", this.onStepWelcomeViewSubmitClick, this);
      app.dispatcher.on("StepDetailView:submitclick", this.onStepDetailViewSubmitClick, this);
      app.dispatcher.on("StepRouteView:gpxuploaded", this.onStepRouteViewGPXUploaded, this);
      app.dispatcher.on("StepRouteEditView:photouploaded", this.onStepRouteEditViewPhotoUploaded, this);
      app.dispatcher.on("StepRouteEditView:galleryphotoclick", this.onStepRouteEditViewGalleryPhotoClick, this);
      app.dispatcher.on("StepRouteEditView:updatedetailsclick", this.onStepRouteEditViewUpdateDetailsClick, this);
      app.dispatcher.on("StepRouteEditView:submitclick", this.onStepRouteEditViewSubmitClick, this);
      app.dispatcher.on("StepPublishedView:submitclick", this.onStepPublishedViewSubmitClick, this);

      this.nTitleState = TITLE_OFF;
      this.mediasModel = new TrailMediasModel();
	  var MediaCollection = Backbone.Collection.extend({
    	comparator: function(item) {
    	  // sort by datetime
          return item.get('tags').datetime;
    	}
	  });
      this.mediaCollection = new MediaCollection();    

      $('#trail_map_view').addClass('map_large');

      var self = this;
      $(window).resize(function() {
        self.handleResize();
      });    

      // Trail Map    
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
      
      // Step Welcome
      this.stepWelcomeView = new StepWelcomeView({ el: '#step_welcome_view', model: this.model });
      if (!nTrail) {
        $('#step_welcome_view').show();
        this.stepWelcomeView.render();
      }    
      // Step Detail
      this.stepDetailView = new StepDetailView({ el: '#step_detail_view', model: this.model });
//      $('#step_detail_view').show();
//      this.stepDetailView.render();    
      // Step Rpute
      this.stepRouteView = new StepRouteView({ el: '#step_route_view', model: this.model });
//      $('#step_route_view').show();    
//      this.stepRouteView.render();
      // Step Route Edit
      this.stepRouteEditView = new StepRouteEditView({ el: '#step_route_edit_view', model: this.model, mediaCollection: this.mediaCollection });
      if (nTrail) {
        $('#step_route_edit_view').show();          
        self.stepRouteView.render();
//        self.stepRouteEditView.render();      	
      }
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
    showTitle: function(){
      if (this.nTitleState != TITLE_OFF) {
        return;
      }
          
      this.nTitleState = TITLE_ON;

      $('#trail_info').addClass('tb-move');
      $('#trail_info').css('top', 24);
      
      var self = this;          
      this.nTitleTimer = setTimeout(function() {
        self.hideTitle();
      }, TITLE_TIMER);                  
    },    
    hideTitle: function(){
      if (this.nTitleState != TITLE_ON) {
        return;
      }    
      this.nTitleState = TITLE_OFF;
      
      $('#trail_info').css('top', -300);        
    },
    setTitles: function(){          	
      // set title
      if (this.model.get('value').route.name != '' && this.model.get('value').route.name != undefined) {
        $('#trail_info').show();
        $('#trail_info .event_name').html(this.model.get('value').route.name);
        $('#trail_info .event_name').css('visibility', 'visible');
      }
      if (this.model.get('value').route.region != '' && this.model.get('value').route.region != undefined) {
        $('#trail_info').show();
        $('#trail_info .trail_name').html(this.model.get('value').route.region);
        $('#trail_info .trail_name').css('visibility', 'visible');
      }
    },
    getTrail: function(){
      var self = this;
      
      this.model.fetch({
        success: function () {
      	  self.setTitles();           
          self.trailMapView.render();          
          self.getTrailMedia();
          self.showTitle();                 
          self.stepRouteEditView.render();      	
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
            
      this.trailMapView.render();          
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    },   
    onStepRouteViewGPXUploaded: function(step2View){
	  $('#trail_map_view').removeClass('map_large');
	  $('#trail_map_view').addClass('map_small');
  
      $('#step_route_view').hide();
      $('#step_route_edit_view').show();
  
      this.handleResize();
    	
      // mla test
      if (nTrail) {
      	this.model.set('id', nTrail);
      }
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
    onStepRouteEditViewGalleryPhotoClick: function(mediaID){
      this.trailMapView.selectMarker(mediaID);    
	},    
    onStepRouteEditViewUpdateDetailsClick: function(stepRouteEditView){      
      var jsonObj = {'name':this.model.get('value').route.name, 'region':this.model.get('value').route.region, 'route_category_id':this.model.get('value').route.route_category_id};
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
	},    	
    onStepRouteEditViewSubmitClick: function(stepRouteEditView){
      var jsonObj = {'publish':true};
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
    	      
/*      
name (string)
region (string)
about (string)
publish (boolean)
route_type_id (integer)
route_category_id (integer)      
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
