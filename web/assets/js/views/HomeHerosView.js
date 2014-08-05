define([
  'underscore', 
  'backbone',
  'views/HomeHeroView'
], function(_, Backbone, HomeHeroView){

  var HERO_SLIDES_PLAY = 0;
  var HERO_SLIDES_STOP = 1;

  var HERO_TIMER = 10000;

  var HomeHerosView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("HomeHeroView:ready", this.onHomeHeroViewReady, this);

	  this.arrHeros = [];
	  this.nCurrHero = 0;
	  this.nLoadingHero = 0;
	  this.bHeroReady = false;
	  this.bWaiting = false;
	  this.bFirstHero = true;      
	  this.nState = HERO_SLIDES_PLAY;
	  
	  var self = this;
	  // nav btns
	  $('.button', $(this.el)).mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');	  	
	  });
	  $('.button', $(this.el)).click(function(evt){
	  	self.nState = HERO_SLIDES_STOP;
	  	
	    self.bWaiting = true;     
	    self.loadHero(Number($(this).attr('data-slide')));	  	
	  });
    },   
    render: function(){
      var self = this;
      
      var homeHeroView = null, heroModel = null;
      $('.hero_image', $(this.el)).each(function(index) {      	
      	var elOverlay = $('.hero_overlay:eq('+index+')', $(self.el));
	    homeHeroView = new HomeHeroView({ el: this, elOverlay: elOverlay, pos: index });
	    homeHeroView.render();
	    self.arrHeros.push(homeHeroView);      	
      });
	  // first hero       
	  this.bWaiting = true;     
	  this.loadHero(this.nLoadingHero);

      return this;    	
	},
    updateNav: function(){
	  $('.button', $(this.el)).removeClass('active');    	
	  $('.button:eq('+this.nLoadingHero+')', $(this.el)).addClass('active');    	
	},	
    prevHero: function(){
	  this.nState = HERO_SLIDES_STOP;
	    	
	  // no more slides          	    	
	  if (this.arrHeros.length < 2) {
	  	return;
	  }          	    	
    	
      var nHero = this.nCurrHero;
	  if (nHero-1 < 0) {
        nHero = this.arrHeros.length-1;      		
      } 
      else {
        nHero--;      	
      }      
	  this.bWaiting = true;           
	  this.loadHero(nHero);     	    	
    },
    nextHero: function(){
      this.nState = HERO_SLIDES_STOP;
    	
	  // no more slides          	    	
	  if (this.arrHeros.length < 2) {
	  	return;
	  }          	    	
    	
      var nHero = this.nCurrHero;
	  if (nHero+1 >= this.arrHeros.length) {
        nHero = 0;      	
      } 
      else {
        nHero++;      		
      }      
	  this.bWaiting = true;           
	  this.loadHero(nHero);     	    	
    },
    loadHero: function(nHero){
      this.nLoadingHero = nHero;
      
      this.bHeroReady = false;
      this.arrHeros[this.nLoadingHero].load();
    },
    checkpoint: function(){
      var self = this;
      
      if (this.bWaiting) {
      	this.updateNav();
      }
                        
      if (this.bWaiting && this.bHeroReady) {      	
        $('#tb-loader-overlay').fadeOut();	
      	
      	this.bWaiting = false;
      	
      	var nDelay = 2000;
      	if (this.bFirstHero) {
      	  this.bFirstHero = false;
      	  nDelay = 0;
      	}
      	
  		this.transition();
      }

	  // still waiting - show loader      
      if (this.bWaiting) {
        $('#tb-loader-overlay').fadeIn();	
      }      
    },
    transition: function(){
      var self = this;

      this.arrHeros[this.nCurrHero].hide();
      this.arrHeros[this.nCurrHero].setZIndex(1, 1);

      this.nCurrHero = this.nLoadingHero;
      
      this.arrHeros[this.nCurrHero].show();      
      this.arrHeros[this.nCurrHero].setZIndex(2, 2);

	  // no more slides          	    	
	  if (this.arrHeros.length < 2) {
	  	return;
	  }          	    	
          	    	
      // load next hero
      if (this.nLoadingHero+1 >= this.arrHeros.length) {
        this.nLoadingHero = 0;      		
      }
      else {
        this.nLoadingHero++;      		
      }
      
      this.loadHero(this.nLoadingHero);
      	
  	  if (this.nHeroTimer) {
  	    clearTimeout(this.nHeroTimer);
  	  }
      	
      this.nHeroTimer = setTimeout(function() {
        self.onHeroTimer();
      }, HERO_TIMER);           	    	
    },
    onHeroTimer: function(){
      if (this.nState != HERO_SLIDES_PLAY) {
      	return;
      } 
      this.bWaiting = true;
      this.checkpoint();
    },
    onHomeHeroViewReady: function(homeHeroView){
      // is this the one we're waiting for?
      if (homeHeroView.nPos == this.nLoadingHero) {
        this.bHeroReady = true;      
        this.checkpoint();      	
      }
	}	
	
  });

  return HomeHerosView;
});
