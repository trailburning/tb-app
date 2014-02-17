define([
  'underscore', 
  'backbone',
  'views/OverlayView',  
  'views/TrailUploadPhotoView',
  'views/TrailUploadPhotoProgressView',
  'views/TrailSlideshowView'  
], function(_, Backbone, OverlayView, TrailUploadPhotoView, TrailUploadPhotoProgressView, TrailSlideshowView){

  var STATE_UPLOAD = 0;

  var StepRouteEditView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#stepRouteEditViewTemplate').text());        
      
      app.dispatcher.on("TrailUploadPhotoView:upload", this.onTrailUploadPhotoViewUpload, this);      
      app.dispatcher.on("TrailUploadPhotoView:uploaded", this.onTrailUploadPhotoViewUploaded, this);
      app.dispatcher.on("TrailUploadPhotoView:uploadProgress", this.onTrailUploadPhotoViewUploadProgress, this);

      app.dispatcher.on("TrailMapView:mediaclick", this.onTrailMapViewMediaClick, this);
      app.dispatcher.on("TrailMapView:removemedia", this.onTrailMapViewRemoveMedia, this);
      app.dispatcher.on("TrailMapView:movedmedia", this.onTrailMapViewMoveMedia, this);

      app.dispatcher.on("TrailSlideshowView:mediaclick", this.onTrailSlideshowViewMediaClick, this);
      app.dispatcher.on("TrailSlideshowView:mediaupdate", this.onTrailSlideshowViewMediaUpdate, this);

      this.nState = STATE_UPLOAD;
      this.timezoneData = null;      
      this.bRendered = false;
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
                            
	  var self = this;                            
                            
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      this.overlayView = new OverlayView({ el: '#overlay_view', model: this.model });
      this.trailUploadPhotoView = new TrailUploadPhotoView({ el: '#uploadPhoto_view', model: this.model });
      this.trailSlideshowView = new TrailSlideshowView({ el: '#slideshow_view', collection: this.options.mediaCollection });

      this.trailUploadPhotoView.render();          

	  this.renderTrailDetail();
            
      $('.submit', $(this.el)).click(function(evt) {
        // fire event
        app.dispatcher.trigger("StepRouteEditView:submitclick", self);                        
      });
            
      return this;
    },
    renderTrailDetail: function(){   
      var self = this;
       
      $('#form_trail_name').val(this.model.get('value').route.name);
      $('#form_trail_region').val(this.model.get('value').route.region);
      $('#form_trail_notes').val('');
      $('.update_details', $(this.el)).click(function(evt) {      
        self.model.get('value').route.name = $('#form_trail_name').val();
        self.model.get('value').route.region = $('#form_trail_region').val();
//        self.model.get('value').route.about = $('#form_trail_notes').val('');
		self.model.get('value').route.route_category_id = $('#trail_types').find('[data-bind="label"]').attr('data-id');
		
	  	self.renderTrailCard();                      		
        // fire event
        app.dispatcher.trigger("StepRouteEditView:updatedetailsclick", self);                        
      });
            
      // get trail types
	  var elList = $('#trail_types ul', $(this.el));
      var strURL = RESTAPI_BASEURL + 'v1/route_category/list';      
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
          // populate list
	      $.each(data.value.route_types, function(key, routeType) {
	      	elList.append('<li role="presentation" data-id="'+routeType.id+'"><a role="menuitem" tabindex="-1" href="#">'+routeType.name+'</a></li>');
	      });
	      // set curr sel
      	  if (self.model.get('value').route.route_category_id == undefined) {
      		// mla temp - this should always be set in db
      		self.model.get('value').route.route_category_id = 3;
      	  }
      	  var elItem = $('#trail_types li[data-id='+self.model.get('value').route.route_category_id+']');
      	  if (elItem.length) {
   			$('#trail_types').find('[data-bind="label"]').text(elItem.eq(0).text()).attr('data-id', self.model.get('value').route.route_category_id);
      	  }
	  	  // list handler            
	  	  $('.dropdown-menu li', $(self.el)).click(function(evt) { 
        	var $target = $(evt.currentTarget);
   			$target.closest('.dropdown')
      		.find('[data-bind="label"]').text($target.text()).attr('data-id', $target.attr('data-id'))
        	.end()
      		.children('.dropdown-toggle').dropdown('toggle');
 
   			return false; 
	  	  });
	  	  
	  	  self.renderTrailCard();                      
        }
      });            
    },
    renderTrailCard: function(){
      $('.trailcard_panel .trail_card_title', $(this.el)).html(this.model.get('value').route.name);
      $('.trailcard_panel .trail_card_region', $(this.el)).html(this.model.get('value').route.region);
	  // trail_card_category      	
	  $('.trailcard_panel .trail_card_category', $(this.el)).html($('#trail_types li[data-id='+this.model.get('value').route.route_category_id+']').text());        
    },
    renderTrailCardPhoto: function(){
      var self = this;
      
      var model = this.options.mediaCollection.at(0); 
  
	  var elContext = $('.trailcard_panel', $(self.el));
	  $('.image_container', elContext).removeClass('tb-fade-in').css('opacity', 0);
	  
  	  $('.trailcard_panel .photo .image_container', $(this.el)).html('<img src="http://app.resrc.it/o=80/http://s3-eu-west-1.amazonaws.com/'+model.get('versions')[0].path+'" class="resrc scale" border="0"/>');
        
	  // scale images when loaded
      var elImages = $('.scale', elContext);	    
      var imgLoad = imagesLoaded(elImages);
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $('img.scale_image_ready', elContext).imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', elContext).addClass('tb-fade-in');
        $('.image_container', elContext).css('opacity', 1);
      });
    },
    renderSlideshow: function(){
      this.trailSlideshowView.render();          
	},
    onTrailUploadPhotoViewUpload: function(trailUploadPhotoView){
      $('#content_overlay').show();      
      $('#overlay_view').show();
      this.overlayView.render();
      
      this.trailUploadPhotoProgressView = new TrailUploadPhotoProgressView({ el: '#overlayContent_view', model: this.model });
      this.trailUploadPhotoProgressView.render();
    },
    onTrailUploadPhotoViewUploaded: function(trailUploadPhotoView){
      $('#content_overlay').hide();      
      $('#overlay_view').hide();
      // fire event
      app.dispatcher.trigger("StepRouteEditView:photouploaded", trailUploadPhotoView);
    },
    onTrailUploadPhotoViewUploadProgress: function(nProgress){
      this.trailUploadPhotoProgressView.render(nProgress);
    },
    onTrailMapViewMediaClick: function(mediaID){
      this.trailSlideshowView.gotoSlide(mediaID);
	},    
    onTrailMapViewRemoveMedia: function(mediaID){
      // remove from collection
	  this.options.mediaCollection.remove(mediaID);
      this.trailSlideshowView.remove(mediaID);
      
      var strURL = RESTAPI_BASEURL + 'v1/media/' + mediaID;      
      $.ajax({
        url: strURL,
        type: 'DELETE',            
        complete : function(res) {
          console.log('complete');              
        },
        success: function(data) {
          console.log('msg:'+data.message);
        },
      });
    },
    onTrailMapViewMoveMedia: function(mediaID){
      // update gallery
      this.trailSlideshowView.sort();
      
      var model = this.options.mediaCollection.get(mediaID);
      var postData = JSON.stringify(model.toJSON());
      var postArray = {json:postData};

	  console.log('onTrailMapViewMoveMedia:');
      console.log(postData);
      
      var strURL = RESTAPI_BASEURL + 'v1/media/' + mediaID;      
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
    onTrailSlideshowViewMediaClick: function(mediaID){
      // fire event
      app.dispatcher.trigger("StepRouteEditView:galleryphotoclick", mediaID);                              
    },
    onTrailSlideshowViewMediaUpdate: function(){
	  this.renderTrailCardPhoto();
    }
    
  });

  return StepRouteEditView;
});
