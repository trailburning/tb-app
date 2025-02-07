define([
  'underscore', 
  'backbone',
  'models/TrailMediasModel',
  'views/ActivityFeedView',  
  'views/trailmaker/TrailmakerMapView',
  'views/trailmaker/TrailmakerTrailCreateView',
  'views/trailmaker/TrailmakerTrailEditView',
  'views/trailmaker/TrailmakerTrailPublishedView'
], function(_, Backbone, TrailMediasModel, ActivityFeedView, TrailMapView, TrailCreateView, TrailEditView, TrailPublishedView){

  var TITLE_TIMER = 10000;

  var TITLE_OFF = 0;
  var TITLE_ON = 1;

  var TrailmakerView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("TrailCreateView:gpxuploaded", this.onTrailCreateViewGPXUploaded, this);
      app.dispatcher.on("TrailEditView:photouploaded", this.onTrailEditViewPhotoUploaded, this);
      app.dispatcher.on("TrailEditView:removemedia", this.onTrailEditViewRemoveMedia, this);
      app.dispatcher.on("TrailEditView:galleryphotoclick", this.onTrailEditViewGalleryPhotoClick, this);
      app.dispatcher.on("TrailEditView:updatedetailsclick", this.onTrailEditViewUpdateDetailsClick, this);
      app.dispatcher.on("TrailEditView:updatestarphoto", this.onTrailEditViewUpdateStarPhoto, this);
      app.dispatcher.on("TrailEditView:fieldkeypress", this.onTrailEditViewFieldKeypress, this);
      app.dispatcher.on("TrailEditView:submitclick", this.onTrailEditViewSubmitClick, this);
      app.dispatcher.on("TrailEditView:submitclick", this.onTrailPublishedViewSubmitClick, this);

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

	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }

      // Trail Map    
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
      
      // Create Trail
      this.trailCreateView = new TrailCreateView({ el: '#step_route_view', model: this.model });
      if (!TB_TRAIL_ID) {
        $('#step_route_view').show();    
        this.trailCreateView.render();        
      	$('#trail_map_overlay').show();            
  	  	this.handleResize();
      	this.trailMapView.render();                  
      }
      // Trail Edit
      this.trailEditView = new TrailEditView({ el: '#step_route_edit_view', model: this.model, mediaCollection: this.mediaCollection });
      if (TB_TRAIL_ID) {
        $('#step_route_edit_view').show();          
        self.trailCreateView.render();
      }
      // Trail Published
      this.trailPublishedView = new TrailPublishedView({ el: '#step_published_view', model: this.model });
    
  	  this.handleResize();
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
          self.trailEditView.render();
		  self.validateTrailForPublish();      	
        }      
      });        
    },
    getTrailMedia: function(){
      var self = this; 
      
      this.mediasModel.url = TB_RESTAPI_BASEURL + '/v1/route/'+this.model.get('id')+'/medias';
      this.mediasModel.fetch({
        success: function () {
	      var data = self.mediasModel.get('value');
	      $.each(data, function(key, jsonMedia) {
	      	var model = new Backbone.Model(jsonMedia);
			self.trailMapView.addMarker(model, true);
		    self.mediaCollection.add(model);
	      });
	      self.trailEditView.renderSlideshow();
	      self.validateTrailForPublish();   
        }
      });
    },
    validateTrailForPublish: function(){
      var bValid = true;

      $('.publish .err-msg span').hide();
      // check enough photos
      if (this.mediaCollection.length < 3) {
      	$('.publish .err-msg span.photos').show();
      	bValid = false;
	  }
	  if (bValid) {
        // check fields
        if ($('#form_trail_name').val() == '') {
      	  $('.publish .err-msg span.name').show();
      	  bValid = false;
        }	  	
	  }
      
      if (bValid) {
        $('.publish .err-msg').hide();
        $('.publish .submit').attr('disabled', false);      	
      }
      else {
        $('.publish .err-msg').show();
        $('.publish .submit').attr('disabled', true);      	
      }
      return bValid;
    },   
    onTrailEditViewFieldKeypress: function(trailEditView){
      this.validateTrailForPublish();
    },    
    onTrailCreateViewGPXUploaded: function(trailCreateView){
	  $('#trail_map_view').removeClass('map_large');
	  $('#trail_map_view').addClass('map_small');
  
      $('#step_route_view').hide();
      $('#step_route_edit_view').show();
  
      this.handleResize();
    	
      if (TB_TRAIL_ID) {
      	this.model.set('id', TB_TRAIL_ID);
      }
      this.getTrail();      
      
      $('#trail_map_overlay', $(this.el)).hide();
      $('#view_map_btns', $(this.el)).show();
    },    
    onTrailEditViewPhotoUploaded: function(trailUploadPhotoView){
	  for (var nPhoto=0; nPhoto < trailUploadPhotoView.photoData.value.length; nPhoto++) {
	    var data = trailUploadPhotoView.photoData.value[nPhoto];	  
        var model = new Backbone.Model(data);
	  
	    this.mediaCollection.add(model);	  
	    this.trailMapView.addMarker(model, true, "");
	  
	    this.trailEditView.renderSlideshow();	  
	    // select slide
	    this.trailEditView.selectSlideshowSlide(model.id);
	  
	    this.validateTrailForPublish();	  	
	  }    	    	
    },    
    onTrailEditViewGalleryPhotoClick: function(mediaID){
      this.trailMapView.selectMarker(mediaID);    
	},    
    onTrailEditViewRemoveMedia: function(trailEditView){
      this.validateTrailForPublish();
	},    
    onTrailEditViewUpdateStarPhoto: function(trailEditView){
      var self = this;
      
      // get model
      var mediaModel = this.mediaCollection.get(trailEditView.getStarMediaID());    	
      if (!mediaModel) {
      	// must have removed starred photo so set 1st one.
	    mediaModel = this.mediaCollection.at(0);
      }
    	
      var jsonObj = {'media_id':mediaModel.id};
      var postData = JSON.stringify(jsonObj);
      var postArray = {json:postData};

      var strURL = TB_RESTAPI_BASEURL + '/v1/route/' + this.model.id;      
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        data: postArray,
        error: function(data) {
//          console.log('error:'+data.responseText);      
//          console.log(data);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
        }
      });	  
	},
    onTrailEditViewUpdateDetailsClick: function(trailEditView){      
      var self = this;
      
      var jsonObj = {'name':this.model.get('value').route.name, 'region':this.model.get('value').route.region, 'about':this.model.get('value').route.about, 'route_category_id':this.model.get('value').route.route_category_id};
      var postData = JSON.stringify(jsonObj);
      var postArray = {json:postData};

      var strURL = TB_RESTAPI_BASEURL + '/v1/route/' + this.model.id;      
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        data: postArray,
        error: function(data) {
          console.log('error:'+data.responseText);      
//          console.log(data);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
		  // a slug will have been created but we don't know what it is      
	      self.model.get('value').route.bDetailUpdated = true;		            
          self.trailEditView.renderTrailCard();
          self.trailEditView.renderTrailCardPhoto();
        }
      });
	},    		
    onTrailEditViewSubmitClick: function(trailEditView){    	
      if (!this.validateTrailForPublish()) {
      	return;
      }
    
      // also update details
      this.model.get('value').route.name = $('#form_trail_name').val();
      this.model.get('value').route.region = $('#form_trail_region').val();
      this.model.get('value').route.about = $('#form_trail_notes').val();
	  this.model.get('value').route.route_category_id = $('#trail_types').find('[data-bind="label"]').attr('data-id');
    	
      var jsonObj = {'publish':true,'name':this.model.get('value').route.name, 'region':this.model.get('value').route.region, 'about':this.model.get('value').route.about, 'route_category_id':this.model.get('value').route.route_category_id};
      var postData = JSON.stringify(jsonObj);
      var postArray = {json:postData};
      
      var strURL = TB_RESTAPI_BASEURL + '/v1/route/' + this.model.id;      
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        data: postArray,
        error: function(data) {
//          console.log('error:'+data.responseText);      
//          console.log(data);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
        }
      });
    	      
	  $('#trail_map_view').removeClass('map_small');
	  $('#trail_map_view').addClass('map_large');

      $('#step_route_edit_view').hide();    
      $('#step_published_view').show();    
      this.trailPublishedView.render();
      
      $('#trail_map_overlay').show();
      $('#view_map_btns', $(this.el)).hide();

      this.handleResize();
      this.trailMapView.render();
      
      $("body").animate({scrollTop:0}, '500', 'swing');
    },
    
  });

  return TrailmakerView;
});
