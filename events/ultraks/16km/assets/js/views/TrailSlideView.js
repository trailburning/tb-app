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
      this.nPanelWidth = 0;
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
      console.log('TrailSlideView:gotoSlide:'+nSlide+' : '+this.nCurrSlide);
      
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
      photoView.render(this.nPanelWidth);            
      $('.image', photoView.el).resizeToParent();
      
      this.checkSlideState();
    },    
    addMedia: function(mediaModel){
      var photoView = new TrailSlidePhotoView({ model: mediaModel });
      this.arrSlidePhotos.push(photoView);
    },
    render: function(nPanelWidth){
      console.log('TrailSlideView:render');
        
      this.nPanelWidth = nPanelWidth;
        
      if (!this.model) {
        return;
      }

      // already rendered?  Just update
      if (this.bRendered) {
        // update container width
        $('.photos_container', this.el).width(nPanelWidth);        
        if (this.nCurrSlide >= 0) {
          var photoView = this.arrSlidePhotos[this.nCurrSlide];
          photoView.render(this.nPanelWidth);
        }
        return;         
      }        
                
      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      // update container width
      $('.photos_container', this.el).width(nPanelWidth);        
                
      for (var nMedia=0; nMedia < this.arrSlidePhotos.length; nMedia++) {
        var photoView = this.arrSlidePhotos[nMedia];
        $('.photos_container', this.el).append(photoView.el);                        
      }
            
      this.bRendered = true;
                        
      return this;
    },
    checkSlideState: function(){
      console.log('TrailSlideView:checkSlideState:'+this.bSlideReady+' : '+this.bWaitingForSlide);
      
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
      var photoView = this.arrSlidePhotos[this.nCurrSlide];
//      console.log('ready:'+photoView.model.cid+' : '+trailSlidePhotoView.model.cid);
      if (photoView.model.cid == trailSlidePhotoView.model.cid) {
        this.bSlideReady = true;
        this.checkSlideState();
      }      
    }
  });

  return TrailSlideView;
});
