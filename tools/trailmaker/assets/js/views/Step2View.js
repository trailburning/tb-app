define([
  'underscore', 
  'backbone',
  'views/TrailMapView',
  'views/TrailUploadGPXView',
  'views/TrailUploadGPXProgressView'  
], function(_, Backbone, TrailMapView, TrailUploadGPXView, TrailUploadGPXProgressView){

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
    getTrail: function(){
      var self = this;    
      // get trail    
      this.model.fetch({
        success: function () {
          self.getTimeZone();
        }      
      });        
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
              
//      this.model.id = 78;
//      this.model.destroy();
              
      var self = this;              
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
        
      this.trailUploadGPXView = new TrailUploadGPXView({ el: '#uploadGPX_view', model: this.model });
      this.trailUploadGPXProgressView = new TrailUploadGPXProgressView({ el: '#uploadGPXprogress_view', model: this.model });
      this.trailMapView = new TrailMapView({ el: '#trail_map_view', model: this.model, timezoneData: this.timezoneData });

      this.trailUploadGPXView.render();          
      this.trailMapView.render();          
        
      return this;
    },
    onTrailUploadGPXViewUploaded: function(trailUploadView){
      console.log('onTrailUploadGPXViewUploaded : '+this.model.id);
      
      this.getTrail();
      
      $('.panel_container', $(this.el)).hide();
    },
    onTrailUploadGPXViewUploadProgress: function(nProgress){
      this.trailUploadGPXProgressView.render(nProgress);
    }    
  });

  return Step2View;
});
