define([
  'underscore', 
  'backbone',
  'views/TrailUploadGPXView',
  'views/TrailUploadGPXProgressView',  
  'views/TrailUploadGPXErrorView',
  'views/TrailUploadPhotoView',
  'views/TrailUploadPhotoProgressView',
  'views/TrailSlideshowView'  
], function(_, Backbone, TrailUploadGPXView, TrailUploadGPXProgressView, TrailUploadGPXErrorView, TrailUploadPhotoView, TrailUploadPhotoProgressView, TrailSlideshowView){

  var STATE_UPLOAD = 0;

  var TrailmakerTrailCreateView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#stepRouteViewTemplate').text());        
      
      app.dispatcher.on("TrailUploadGPXView:uploaded", this.onTrailUploadGPXViewUploaded, this);
      app.dispatcher.on("TrailUploadGPXView:uploadProgress", this.onTrailUploadGPXViewUploadProgress, this);
      app.dispatcher.on("TrailUploadGPXView:error", this.onTrailUploadGPXViewError, this);
      
      app.dispatcher.on("TrailUploadPhotoView:uploaded", this.onTrailUploadPhotoViewUploaded, this);
      app.dispatcher.on("TrailUploadPhotoView:uploadProgress", this.onTrailUploadPhotoViewUploadProgress, this);

      app.dispatcher.on("TrailMapView:removemedia", this.onTrailMapViewRemoveMedia, this);
      app.dispatcher.on("TrailMapView:movedmedia", this.onTrailMapViewMoveMedia, this);

      app.dispatcher.on("TrailSlideshowSlideView:click", this.onTrailSlideshowSlideViewClick, this);

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
      this.trailUploadGPXErrorView = new TrailUploadGPXErrorView({ el: '#uploadGPXerror_view', model: this.model });

      this.trailUploadGPXView.render();
      
      $('.submit', $(this.el)).click(function(evt) {
        // fire event
        app.dispatcher.trigger("TrailCreateView:submitclick", self);                        
      });

      if (TB_TRAIL_ID) {
         $('#step_route_view .panel_container').hide();      
         $('.map_step_container', $(this.el)).show();  
          // fire event
         app.dispatcher.trigger("TrailCreateView:gpxuploaded", self);           
      }
        
      return this;
    },
    renderSlideshow: function(){
      this.trailSlideshowView.render();          
	},
    onTrailUploadGPXViewUploaded: function(trailUploadGPXView){
      // fire event
      app.dispatcher.trigger("TrailCreateView:gpxuploaded", self);                        
    },
    onTrailUploadGPXViewUploadProgress: function(nProgress){
      this.trailUploadGPXProgressView.render(nProgress);
    },
    onTrailUploadGPXViewError: function(){
      $('#uploadGPX_view').hide();
      $('#uploadGPXprogress_view').hide();
      $('#uploadGPXerror_view').show();
      this.trailUploadGPXErrorView.render(0);
	}    
    
  });

  return TrailmakerTrailCreateView;
});
