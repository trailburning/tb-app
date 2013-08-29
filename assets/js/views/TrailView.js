var app = app || {};

define([
  'underscore', 
  'backbone',
  'models/TrailMediaModel',  
  'views/TrailUploadView',
  'views/TrailMediaUploadView',
  'views/TrailUploadProgressView',
  'views/TrailDisplayView'
], function(_, Backbone, TrailMediaModel, TrailUploadView, TrailMediaUploadView, TrailUploadProgressView, TrailDisplayView){

  var TrailView = Backbone.View.extend({
    initialize: function(){
      this.model.on("sync", this.onTrailSynced, this);
      
      app.dispatcher.on("TrailUploadView:uploaded", this.onTrailUploadViewUploaded, this);
      app.dispatcher.on("TrailUploadView:uploadProgress", this.onTrailUploadViewUploadProgress, this);

      app.dispatcher.on("TrailMediaUploadView:uploaded", this.onTrailMediaUploadViewUploaded, this);
      app.dispatcher.on("TrailMediaUploadView:uploadProgress", this.onTrailMediaUploadViewUploadProgress, this);
      
      this.mediaModel = new TrailMediaModel();
      
      this.trailUploadView = new TrailUploadView({ el: '#trailuploadview', model: this.model });    
      this.trailMediaUploadView = new TrailMediaUploadView({ el: '#trailmediauploadview', model: this.model });    
      this.trailUploadProgressView = new TrailUploadProgressView({ el: '#trailuploadprogressview', model: this.model });
      this.trailDisplayView = new TrailDisplayView({ el: '#traildisplayview', model: this.model });
    },            
    render: function(){      
      this.trailUploadView.render();
      this.trailMediaUploadView.render();      
      this.trailUploadProgressView.render();      
      this.trailDisplayView.render();
                                                   
      return this;
    },
    test: function(){
      this.trailDisplayView.test();
    },      
    getTrail: function(){
      var self = this; 
      
      console.log('Fetch ID:'+this.model.get('id'));            
      this.model.fetch({
        success: function () {
          console.log('Fetched');
          console.log(self.model);
          self.trailDisplayView.render();
          
          self.getTrailMedia();
          
          // delete          
//          self.model.destroy();
        }      
      });      
    },
    getTrailMedia: function(){
      var self = this; 
      
      this.mediaModel.url = RESTAPI_BASEURL + 'v1/route/'+this.model.get('id')+'/medias';
      this.mediaModel.fetch({
        success: function () {
          console.log('Fetched media');
          self.trailDisplayView.renderMedia(self.mediaModel);
        }
      });
    },
    onTrailSynced: function(){
      console.log('TrailView:onTrailSynced : '+this.model.get('id'));
    },
    onTrailUploadViewUploaded: function(trailUploadView){
//      $('#trailuploadview').hide();
//      $('#trailmediauploadview').show();
      
      this.getTrail();
    },
    onTrailMediaUploadViewUploaded: function(trailMediaUploadView){
      this.getTrailMedia();
    },
    onTrailUploadViewUploadProgress: function(nProgress){
      this.trailUploadProgressView.render(nProgress);
    },
    onTrailMediaUploadViewUploadProgress: function(nProgress){
      this.trailUploadProgressView.render(nProgress);
    }
  });

  return TrailView;
});
