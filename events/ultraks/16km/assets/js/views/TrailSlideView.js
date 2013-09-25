define([
  'underscore', 
  'backbone',
  'views/TrailSlidePhotoView'
], function(_, Backbone, TrailSlidePhotoView){
  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;
  
  var HOLD_SLIDE = 8000;

  var TrailSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlideViewTemplate').text());        
            
      app.dispatcher.on("TrailSlidePhotoView:imageready", this.onSlidePhotoReady, this);
            
      this.bRendered = false;
      this.nSlideShowState = SLIDESHOW_INIT;
      this.arrSlidePhotos = [];
      this.nCurrSlide = -1;
      this.nPanelWidth = 0;
      this.slideTimer = null;
      this.bSlideReady = false;
      this.bWaitingForSlide = false;
    },            
    show: function(){
      $(this.el).show();
    },
    hide: function(){
      $(this.el).hide();
    },
    startSlideShow: function(){    
      this.nSlideShowState = SLIDESHOW_PLAYING;
          
      this.nextSlide();
    },
    stopSlideShow: function(){    
      this.nSlideShowState = SLIDESHOW_STOPPED;
      
      if (this.slideTimer) {
        clearTimeout(this.slideTimer);
      }
    },    
    nextSlide: function(){    
      var nSlide = this.nCurrSlide; 
      if (nSlide < this.arrSlidePhotos.length-1) {
        nSlide++;                               
      }
      else {
        nSlide = 0;
      }
      this.gotoSlide(nSlide);
    },    
    gotoSlide: function(nSlide){
      console.log('s:'+nSlide+' : '+this.nCurrSlide);
      
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
        if (this.nCurrSlide >= 0) {
          var photoView = this.arrSlidePhotos[this.nCurrSlide];
          photoView.render(this.nPanelWidth);
        }
        return;         
      }        
                
      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
                
      for (var nMedia=0; nMedia < this.arrSlidePhotos.length; nMedia++) {
        var photoView = this.arrSlidePhotos[nMedia];
        $('.photos_container', this.el).append(photoView.el);                        
      }
            
      this.bRendered = true;
                        
      return this;
    },
    checkSlideState: function(){
      console.log('checkSlideState:'+this.bSlideReady+' : '+this.bWaitingForSlide);
      
      var self = this;
      
      if (this.bSlideReady && this.bWaitingForSlide) {
        this.bWaitingForSlide = false;
        
        var photoView = this.arrSlidePhotos[this.nCurrSlide];        
        photoView.show();
        
        // start timer
        if (this.slideTimer) {
          clearTimeout(this.slideTimer);
        }
        
        if (this.nSlideShowState == SLIDESHOW_PLAYING) {
          this.slideTimer = setTimeout(function() {
            self.bWaitingForSlide = true;
            self.nextSlide();          
            self.checkSlideState();
          }, HOLD_SLIDE);
        }
      }
    },    
    onSlidePhotoReady: function(trailSlidePhotoView){
      var photoView = this.arrSlidePhotos[this.nCurrSlide];
      console.log('ready:'+photoView.model.cid+' : '+trailSlidePhotoView.model.cid);
      if (photoView.model.cid == trailSlidePhotoView.model.cid) {
        this.bSlideReady = true;
        this.checkSlideState();
      }      
    }
  });

  return TrailSlideView;
});
