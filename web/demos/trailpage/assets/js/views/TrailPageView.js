define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var MIN_HEIGHT = 486;
  
  var AppView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.nPlayerHeight = 0;
	  this.bPlayer = false;
      
      this.updatePlayerHeight();
      
      $(window).resize(function() {
        self.handleResize();
      });    
  
      this.updatePlayerHeight();
      
      this.loadHero();
      this.loadPlayer();
      
      $('.btnPlayer').click(function(evt){
        if (self.bPlayer) {
      	  self.bPlayer = false;

          $('#trail_player_container').addClass('tb-size');
          $('#trail_player_container .hero').addClass('tb-move-vert');
      	
      	  $('#trail_player_container').height(486);      
      	  $('#trail_player_container .hero').css('top', 0);
      	  $('#trail_player_container .player').css('top', -50);
        }
        else {
      	  self.bPlayer = true;

          $('#trail_player_container').addClass('tb-size');
          $('#trail_player_container .hero').addClass('tb-move-vert');
      	
      	  self.updatePlayerHeight();
      	
      	  $('#trail_player_container .hero').css('top', self.nPlayerHeight + 50);
      	  $('#trail_player_container .player').css('top', 0);      	      	
      	
      	  $("img.scale_image_ready").imageScale();
        }
      });        
    },   
    loadHero: function(){
      var imgLoad = imagesLoaded($('.hero .scale'));
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
          // update pos
          $(imgLoad.images[i].img).imageScale();
        }
        // fade in - delay adding class to ensure image is ready  
        $('.hero .fade_on_load').addClass('tb-fade-in');
        $('.hero .image_container').css('opacity', 1);
      });
	  // invoke resrc      
      resrc.resrc($('.hero .scale'));        
    },
    loadPlayer: function(){
      var imgLoad = imagesLoaded($('.player .scale'));
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // fade in - delay adding class to ensure image is ready  
        $('.player .fade_on_load').addClass('tb-fade-in');
        $('.player .image_container').css('opacity', 1);
      });
	  // invoke resrc      
      resrc.resrc($('.player .scale'));        
    },    
    updatePlayerHeight: function(){
      if (!this.bPlayer) {
      	return;
      }
    
      var nPlayerHeight = 0;      
      var elContentView = $('#bodyview');
      var nContentY = elContentView.position().top;
      console.log('t:'+nContentY);
      
      nPlayerHeight = Math.round(elContentView.width() * 0.746875);                  
      // check height fits
      if ((nPlayerHeight+nContentY) > $(window).height()) {  
        nPlayerHeight = $(window).height() - nContentY;
      }
      if (nPlayerHeight < MIN_HEIGHT) {
        nPlayerHeight = MIN_HEIGHT;
      }
      
      // height of white bar
      nPlayerHeight -= 8;
      this.nPlayerHeight = nPlayerHeight;
      $('#trail_player_container').height(this.nPlayerHeight);

      $('#trail_player_container .player').height(this.nPlayerHeight);
      $('#trail_player_container .player .foreground').height(this.nPlayerHeight);
      // force height update for imageScale
      $('#trail_player_container .player .image_container').height(this.nPlayerHeight);
      
      $('#trail_player_container .hero').css('top', this.nPlayerHeight);      
      
      $("img.scale_image_ready").imageScale();	      	
    },
    handleResize: function(){
      // remove transition to avoid seeing grey beneath image when resizing
      $('#trail_player_container').removeClass('tb-size');
      $('#trail_player_container .hero').removeClass('tb-move-vert');
      
      this.updatePlayerHeight();
      
      $("img.scale_image_ready").imageScale();      
    },
  });

  return AppView;
});
