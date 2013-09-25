define([
  'underscore', 
  'backbone',
  'views/TrailSlidePhotoView'
], function(_, Backbone, TrailSlidePhotoView){
  var HOLD_SLIDE = 8000;

  var TrailSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlideViewTemplate').text());        
            
      app.dispatcher.on("TrailSlidePhotoView:imageready", this.onSlidePhotoReady, this);
            
      this.bRendered = false;
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
                
      var jsonMarkers = this.model.get('value');

      $.each(jsonMarkers, function(key, marker) {
        var photoModel = new Backbone.Model(marker);
        var photoView = new TrailSlidePhotoView({ model: photoModel });
        self.arrSlidePhotos.push(photoView);
        $('.photos_container', this.el).append(photoView.el);                
      });
            
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
        this.slideTimer = setTimeout(function() {
          self.bWaitingForSlide = true;
          self.nextSlide();          
          self.checkSlideState();
        }, HOLD_SLIDE);
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
