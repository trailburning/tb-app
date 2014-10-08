define([
  'underscore', 
  'backbone',
  'views/TrailSlideView'
], function(_, Backbone, TrailSlideView){
  
  var TrailSlidesView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailSlidesViewTemplate').text());        
            
      app.dispatcher.on("TrailSlideView:imageready", this.onSlideReady, this);
            
      this.bRendered = false;
      this.arrSlidePhotos = [];
      this.nOldSlide = -1;
      this.nCurrSlide = -1;
      this.nHeroSlideId = -1;
      this.nHeroSlide = 0;
      this.bSlideReady = false;
      this.bWaitingForSlide = false;
    },            
    show: function(){      
      $(this.el).fadeIn();
    },
    hide: function(){
      $(this.el).fadeOut();
    },    
    getHeroSlide: function(){
      return this.nHeroSlide;
    },
    setHeroSlideId: function(nId){
      this.nHeroSlideId = nId;
    },
    gotoHeroSlide: function(){
      this.gotoSlide(this.nHeroSlide);
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
      
   	  if (mediaModel.id == this.nHeroSlideId) {
        this.nHeroSlide = this.arrSlidePhotos.length - 1; 	
      }
    },
    render: function(){
      if (!this.model) {
        return;
      }

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
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      // update container width
      $('.image_container', this.el).width($('#appview').width());
      $('.photos_container', this.el).width($('#appview').width());                        
      for (var nMedia=0; nMedia < this.arrSlidePhotos.length; nMedia++) {
        var photoView = this.arrSlidePhotos[nMedia];
        $('.photos_container', this.el).append(photoView.el);      
      }
      this.buildBtns();
            
      this.bRendered = true;
                        
      return this;
    },
    buildBtns: function(){    
      var self = this;
      
      $('.slide_btns .left .arrow', $(this.el)).addClass('tb-move-horiz');
      $('.slide_btns .right .arrow', $(this.el)).addClass('tb-move-horiz');
            
      $('.slide_btns .left .hot', $(this.el)).click(function(evt){
        // fire event
        app.dispatcher.trigger("TrailSlidesView:clickslideprev", self);                
      });
    	
      $('.slide_btns .left .hot', $(this.el)).mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
      });      

      $('.slide_btns .left', $(this.el)).mouseover(function(evt){
      	$('.arrow', $(this)).css('left', 18);
 		$('.hot', $(this)).css('opacity', 1);
      });      

      $('.slide_btns .left', $(this.el)).mouseout(function(evt){
      	$('.arrow', $(this)).css('left', -100);
		$('.hot', $(this)).css('opacity', 0);
      });      

      $('.slide_btns .right .hot', $(this.el)).click(function(evt){
        // fire event
        app.dispatcher.trigger("TrailSlidesView:clickslidenext", self);                
      });
    	
      $('.slide_btns .right .hot', $(this.el)).mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
      });      

      $('.slide_btns .right', $(this.el)).mouseover(function(evt){
      	$('.arrow', $(this)).css('right', 18);
 		$('.hot', $(this)).css('opacity', 1);
      });      

      $('.slide_btns .right', $(this.el)).mouseout(function(evt){
      	$('.arrow', $(this)).css('right', -100);
		$('.hot', $(this)).css('opacity', 0);
      });      
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

  return TrailSlidesView;
});
