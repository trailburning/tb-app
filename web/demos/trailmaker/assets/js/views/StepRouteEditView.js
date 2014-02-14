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

      this.nState = STATE_UPLOAD;
      this.timezoneData = null;      
      this.bRendered = false;
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
                            
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      this.overlayView = new OverlayView({ el: '#overlay_view', model: this.model });
      this.trailUploadPhotoView = new TrailUploadPhotoView({ el: '#uploadPhoto_view', model: this.model });
      this.trailSlideshowView = new TrailSlideshowView({ el: '#slideshow_view', collection: this.options.mediaCollection });

      this.trailUploadPhotoView.render();          

      $('#form_trail_name').val(this.model.get('value').route.name);
      $('#form_trail_region').val(this.model.get('value').route.region);
      $('#form_trail_notes').val('');
      $('.update_details', $(this.el)).click(function(evt) {
      });
            
      $('.submit', $(this.el)).click(function(evt) {
        // fire event
        app.dispatcher.trigger("StepRouteEditView:submitclick", self);                        
      });

	  // scale images when loaded
      var imgLoad = imagesLoaded('.scale');
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $("img.scale_image_ready").imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load').addClass('tb-fade-in');
        $('.image_container').css('opacity', 1);
      });

      return this;
    },
    renderSlideshow: function(){
      this.trailSlideshowView.render();          
	},
    onTrailUploadPhotoViewUpload: function(trailUploadPhotoView){
      console.log('onTrailUploadPhotoViewUpload');
      
      $('#content_overlay').show();      
      $('#overlay_view').show();
      this.overlayView.render();
      
      this.trailUploadPhotoProgressView = new TrailUploadPhotoProgressView({ el: '#overlayContent_view', model: this.model });
      this.trailUploadPhotoProgressView.render();
    },
    onTrailUploadPhotoViewUploaded: function(trailUploadPhotoView){
      console.log('onTrailUploadPhotoViewUploaded');
      
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
      app.dispatcher.trigger("StepRouteEditView:galleryPhotoClick", mediaID);                              
    }      	
    
  });

  return StepRouteEditView;
});
