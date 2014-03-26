define([
  'underscore', 
  'backbone',
  'views/TrailSlideView'
], function(_, Backbone, TrailSlideView){

  var TrailMiniSlidesView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailMiniSlidesViewTemplate').text());
      
      app.dispatcher.on("TrailSlideView:imageready", this.onSlideReady, this);
      
      this.bRendered = false;        
      this.arrSlidePhotos = [];
      this.nOldSlide = -1;
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
       
      this.nOldSlide = this.nCurrSlide;  
      this.nCurrSlide = nSlide;
      
      var photoView = this.arrSlidePhotos[nSlide];
      photoView.load();      
    },    
    addMedia: function(mediaModel){
      var photoView = new TrailSlideView({ model: mediaModel, type: 1 });
      this.arrSlidePhotos.push(photoView);
      photoView.render($(this.el).width);
    },    
    render: function(){
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

      this.bRendered = true;

      return this;
    },
    checkpoint: function(){
      var self = this;
      
      if (this.bSlideReady && this.bWaitingForSlide) {
        this.bWaitingForSlide = false;
        
        var photoView;
        // hide old photo
        if (this.nOldSlide >= 0) {
          photoView = this.arrSlidePhotos[this.nOldSlide];      
          photoView.hide();        
        }
        photoView = this.arrSlidePhotos[this.nCurrSlide];
        photoView.show();
      }
    },
    onSlideReady: function(trailSlideView){
      if (trailSlideView.nType != 1) {
        return;
      }
      
      var nCurrCID = trailSlideView.model.cid;      
      var photoView = this.arrSlidePhotos[this.nCurrSlide];
      if (photoView) {
        nCurrCID = photoView.model.cid;
      }
      if (nCurrCID == trailSlideView.model.cid) {        
        this.bSlideReady = true;
        this.checkpoint();
      }      
    }        
  });

  return TrailMiniSlidesView;
});
