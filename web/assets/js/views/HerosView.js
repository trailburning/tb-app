define([
  'underscore', 
  'backbone',
  'views/HeroView'
], function(_, Backbone, HeroView){

  var HERO_SLIDES_PLAY = 0;
  var HERO_SLIDES_STOP = 1;

  var HERO_TIMER = 8000;

  var HerosView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("HeroView:ready", this.onHeroViewReady, this);

	  this.arrHeros = [];
	  this.nCurrHero = 0;
	  this.nLoadingHero = 0;
	  this.bHeroReady = false;
	  this.bWaiting = false;
	  this.bFirstHero = true;      
	  this.nState = HERO_SLIDES_PLAY;
    },   
    render: function(){
      var self = this;
      
      var heroView = null;
      $('.hero_image', $(this.el)).each(function(index) {
	    heroView = new HeroView({ el: this, pos: index });
	    heroView.render();
	    self.arrHeros.push(heroView);      	
      });
	  // first hero       
	  this.bWaiting = true;     
	  
	  // rnd start
	  var min = 0, max = this.arrHeros.length;
	  
	  this.nLoadingHero = Math.floor(Math.random() * (max - min)) + min;
	  this.loadHero(this.nLoadingHero);

      return this;    	
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

      this.nCurrHero = this.nLoadingHero;
      
      this.arrHeros[this.nCurrHero].show();      

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
    onHeroViewReady: function(heroView){
      // is this the one we're waiting for?
      if (heroView.nPos == this.nLoadingHero) {
        this.bHeroReady = true;      
        this.checkpoint();      	
      }
	}	
	
  });

  return HerosView;
});
