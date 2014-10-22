define([
  'underscore', 
  'backbone',
  'views/TrailSlideView'
], function(_, Backbone, TrailSlideView){
  
  var CampaignSlidesView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlidesViewTemplate').text());        
            
      app.dispatcher.on("TrailSlideView:imageready", this.onSlideReady, this);
            
      this.bRendered = false;
      this.arrSlidePhotos = [];
      this.nOldSlide = -1;
      this.nCurrSlide = -1;
      this.bSlideReady = false;
      this.bWaitingForSlide = false;
    },            
    show: function(){      
      $(this.el).css('visibility', 'visible');
      $(this.el).fadeIn(500, 'linear');
    },
    hide: function(){
      $(this.el).fadeOut(500, 'linear');
    },    
    gotoSlide: function(nSlide){
      this.bSlideReady = false;
      this.bWaitingForSlide = true;     
      
      this.nOldSlide = this.nCurrSlide;  
      this.nCurrSlide = nSlide;
      
      if (this.arrSlidePhotos.length) {
        var photoView = this.arrSlidePhotos[nSlide];
        if (!photoView.isLoaded()) {
	      $('#tb-loader-overlay').fadeIn();      		      	
        }
        photoView.load();      
      }
    },        
    addMedia: function(mediaModel){    	
      var photoView = new TrailSlideView({ model: mediaModel, type: 0 });
      this.arrSlidePhotos.push(photoView);
      photoView.render($('#appview').width());
    },
    render: function(){
      // already rendered?  Just update
      if (this.bRendered) {
        // update container width
        $('.photos_container', this.el).width($('#appview').width());        
        $('.image_container', this.el).width($('#appview').width());
        if (this.nCurrSlide >= 0 && this.arrSlidePhotos.length) {
          var photoView = this.arrSlidePhotos[this.nCurrSlide];
          photoView.render();
        }
        return;         
      }        
                
      var self = this;
                
      $(this.el).html(this.template());

      // update container width
      $('.image_container', this.el).width($('#appview').width());
      $('.photos_container', this.el).width($('#appview').width());                        
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
        $('#tb-loader-overlay').fadeOut();	
        
        this.bWaitingForSlide = false;
        
        var photoView;
        // hide old photo
        if (this.nOldSlide >= 0) {
          photoView = this.arrSlidePhotos[this.nOldSlide];      
          photoView.hide();        
        }
                
        photoView = this.arrSlidePhotos[this.nCurrSlide];        
        photoView.show($('#appview').width());
        
	    // pre-load next slide
	    var nNextSlide = 0;
	    if (this.nCurrSlide+1 >= this.arrSlidePhotos.length) {
	      nNextSlide = 0;      		
	    }
	    else {
	      nNextSlide = this.nCurrSlide+1; 
	    }
        var photoNextView = this.arrSlidePhotos[nNextSlide];
        photoNextView.load();        
        
        // fire event
        app.dispatcher.trigger("TrailSlidesView:slideview", this);                
      }
    },    
    onSlideReady: function(trailSlideView){
      if (trailSlideView.nType != 0) {
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

  return CampaignSlidesView;
});
