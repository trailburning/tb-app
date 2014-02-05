define([
  'underscore', 
  'backbone',
  'views/TrailUploadGPXView',
  'views/TrailUploadGPXProgressView',  
  'views/TrailUploadPhotoView',
  'views/TrailUploadPhotoProgressView',
  'views/TrailSlideshowView'  
], function(_, Backbone, TrailUploadGPXView, TrailUploadGPXProgressView, TrailUploadPhotoView, TrailUploadPhotoProgressView, TrailSlideshowView){

  var STATE_UPLOAD = 0;

  var Step2View = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#step2ViewTemplate').text());        
      
      app.dispatcher.on("TrailUploadGPXView:uploaded", this.onTrailUploadGPXViewUploaded, this);
      app.dispatcher.on("TrailUploadGPXView:uploadProgress", this.onTrailUploadGPXViewUploadProgress, this);
      
      app.dispatcher.on("TrailUploadPhotoView:uploaded", this.onTrailUploadPhotoViewUploaded, this);
      app.dispatcher.on("TrailUploadPhotoView:uploadProgress", this.onTrailUploadPhotoViewUploadProgress, this);

      app.dispatcher.on("TrailMapView:removemedia", this.onTrailMapViewRemoveMedia, this);
      app.dispatcher.on("TrailMapView:movedmedia", this.onTrailMapViewMoveMedia, this);

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
              
      this.trailUploadGPXView = new TrailUploadGPXView({ el: '#uploadGPX_view', model: this.model });
      this.trailUploadGPXProgressView = new TrailUploadGPXProgressView({ el: '#uploadGPXprogress_view', model: this.model });

      this.trailUploadPhotoView = new TrailUploadPhotoView({ el: '#uploadPhoto_view', model: this.model });
      this.trailUploadPhotoProgressView = new TrailUploadPhotoProgressView({ el: '#uploadPhotoprogress_view', model: this.model });
      this.trailSlideshowView = new TrailSlideshowView({ el: '#slideshow_view', collection: this.options.mediaCollection });

      this.trailUploadPhotoView.render();          
      this.trailUploadGPXView.render();
      
      $('.submit', $(this.el)).click(function(evt) {
        // fire event
        app.dispatcher.trigger("Step2View:submitclick", self);                        
      });

      // mla test
      this.model.set('id', 148);
      $('#step2_view .panel_container').hide();      
      $('.map_step_container', $(this.el)).show();  
      // fire event
      app.dispatcher.trigger("Step2View:gpxuploaded", self);                        
        
      return this;
    },
    renderSlideshow: function(){
      this.trailSlideshowView.render();          
	},
    onTrailUploadGPXViewUploaded: function(trailUploadGPXView){
      console.log('onTrailUploadGPXViewUploaded : '+this.model.id);

      $('.panel_container', $(this.el)).hide();  
      $('.map_step_container', $(this.el)).show();  

      // fire event
      app.dispatcher.trigger("Step2View:gpxuploaded", self);                        
    },
    onTrailUploadGPXViewUploadProgress: function(nProgress){
      this.trailUploadGPXProgressView.render(nProgress);
    },
    onTrailUploadPhotoViewUploaded: function(trailUploadPhotoView){
      console.log('onTrailUploadPhotoViewUploaded');
      
      // fire event
      app.dispatcher.trigger("Step2View:photouploaded", trailUploadPhotoView);                              
    },
    onTrailUploadPhotoViewUploadProgress: function(nProgress){
      this.trailUploadPhotoProgressView.render(nProgress);
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
    onTrailMapViewMoveMedia: function(){
      this.trailSlideshowView.sort();
    }          
    
  });

  return Step2View;
});
