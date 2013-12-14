define([
  'underscore', 
  'backbone',
  'views/TrailUploadGPXView',
  'views/TrailUploadGPXProgressView'  
], function(_, Backbone, TrailUploadGPXView, TrailUploadGPXProgressView){

  var STATE_UPLOAD = 0;

  var Step2View = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#step2ViewTemplate').text());        
      
      app.dispatcher.on("TrailUploadGPXView:uploaded", this.onTrailUploadGPXViewUploaded, this);
      app.dispatcher.on("TrailUploadGPXView:uploadProgress", this.onTrailUploadGPXViewUploadProgress, this);
      
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

      this.trailUploadGPXView.render();          

      $('.submit', $(this.el)).click(function(evt) {
        // fire event
        app.dispatcher.trigger("Step2View:submitclick", self);                        
      });

      // mla test - ashmei
      this.model.set('id', 132);
      $('#step2_view .panel_container').hide();      
      $('.map_step_container', $(this.el)).show();  
      // fire event
      app.dispatcher.trigger("Step2View:gpxuploaded", self);                        
        
      return this;
    },
    onTrailUploadGPXViewUploaded: function(trailUploadView){
      console.log('onTrailUploadGPXViewUploaded : '+this.model.id);

      $('.panel_container', $(this.el)).hide();  
      $('.map_step_container', $(this.el)).show();  

      // fire event
      app.dispatcher.trigger("Step2View:gpxuploaded", self);                        
    },
    onTrailUploadGPXViewUploadProgress: function(nProgress){
      this.trailUploadGPXProgressView.render(nProgress);
    }    
  });

  return Step2View;
});
