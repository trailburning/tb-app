define([
  'underscore', 
  'backbone',
  'views/TrailSlidePhotoView'
], function(_, Backbone, TrailSlidePhotoView){

  var TrailMiniSlideView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMiniSlideViewTemplate').text());
      
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
      photoView.render($(this.el).width);            
      $('.image', photoView.el).resizeToParent();
      
      this.checkSlideState();      
    },    
    addMedia: function(mediaModel){
      var photoView = new TrailSlidePhotoView({ model: mediaModel });
      this.arrSlidePhotos.push(photoView);
    },    
    render: function(){
      console.log('TrailMiniSlideView:render');

      if (!this.model) {
        return;
      }

      // already rendered?  Just update
      if (this.bRendered) {
        // update container width
        if (this.nCurrSlide >= 0) {
          var photoView = this.arrSlidePhotos[this.nCurrSlide];
          photoView.render($(this.el).width);
        }
        return;         
      }        
                
      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      // update container width
      for (var nMedia=0; nMedia < this.arrSlidePhotos.length; nMedia++) {
        var photoView = this.arrSlidePhotos[nMedia];
        $('.photos_container', this.el).append(photoView.el);                        
      }

      $('.btn', $(this.el)).click(function(evt){
        // fire event
        app.dispatcher.trigger("TrailMiniSlideView:viewbtnclick", self);                
      });

      this.bRendered = true;

      return this;
    },
    checkSlideState: function(){
      console.log('TrailMiniSlideView:checkSlideState:'+this.bSlideReady+' : '+this.bWaitingForSlide);
      
      var self = this;
      
      if (this.bSlideReady && this.bWaitingForSlide) {
        this.bWaitingForSlide = false;
        
        var photoView = this.arrSlidePhotos[this.nCurrSlide];        
        photoView.show();
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

  return TrailMiniSlideView;
});
