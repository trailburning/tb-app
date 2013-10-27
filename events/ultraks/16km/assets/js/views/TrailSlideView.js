define([
  'underscore', 
  'backbone',
  'views/TrailSlidePhotoView'
], function(_, Backbone, TrailSlidePhotoView){
  
  var TrailSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlideViewTemplate').text());        
            
      app.dispatcher.on("TrailSlidePhotoView:imageready", this.onSlidePhotoReady, this);
            
      this.bRendered = false;
      this.arrSlidePhotos = [];
      this.nCurrSlide = -1;
      this.bSlideReady = false;
      this.bWaitingForSlide = false;
    },            
    show: function(){      
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    gotoSlide: function(nSlide){
      this.bSlideReady = false;     
      this.bWaitingForSlide = true;
       
      var photoView = null;      
      // hide curr photo
      if (this.nCurrSlide >= 0) {
        photoView = this.arrSlidePhotos[this.nCurrSlide];      
        photoView.hide();        
      }
      this.nCurrSlide = nSlide;

      photoView = this.arrSlidePhotos[this.nCurrSlide];
      photoView.render($('#appview').width());            
      
      this.checkSlideState();
    },    
    addMedia: function(mediaModel){
      var photoView = new TrailSlidePhotoView({ model: mediaModel, type: 0 });
      this.arrSlidePhotos.push(photoView);
    },
    render: function(){
      if (!this.model) {
        return;
      }

      // already rendered?  Just update
      if (this.bRendered) {
        // update container width
        $('.image_container', this.el).width($('#appview').width());
        $('.photos_container', this.el).width($('#appview').width());        
        if (this.nCurrSlide >= 0) {
          var photoView = this.arrSlidePhotos[this.nCurrSlide];
          photoView.render($('#appview').width());
        }
        return;         
      }        
                
      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      // update container width
      $('.image_container', this.el).width($('#appview').width());
      $('.photos_container', this.el).width($('#appview').width());                        
      for (var nMedia=0; nMedia < this.arrSlidePhotos.length; nMedia++) {
        var photoView = this.arrSlidePhotos[nMedia];
        $('.photos_container', this.el).append(photoView.el);      
        photoView.render(this.nPanelWidth);                  
      }
            
      this.bRendered = true;
                        
      return this;
    },
    checkSlideState: function(){
      var self = this;
      
      if (this.bSlideReady && this.bWaitingForSlide) {
        this.bWaitingForSlide = false;
        
        var photoView = this.arrSlidePhotos[this.nCurrSlide];        
        photoView.show();
        
        // fire event
        app.dispatcher.trigger("TrailSlideView:slideview", self);                
      }
    },    
    onSlidePhotoReady: function(trailSlidePhotoView){   
      if (trailSlidePhotoView.nType != 0) {
        return;
      }
         
      var nCurrCID = trailSlidePhotoView.model.cid;      
      var photoView = this.arrSlidePhotos[this.nCurrSlide];
      if (photoView) {
        nCurrCID = photoView.model.cid;
      }      
      if (nCurrCID == trailSlidePhotoView.model.cid) {
        this.bSlideReady = true;
        this.checkSlideState();
      }      
    }
  });

  return TrailSlideView;
});
