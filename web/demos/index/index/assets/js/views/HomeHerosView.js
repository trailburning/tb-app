define([
  'underscore', 
  'backbone',
  'views/HomeHeroView'
], function(_, Backbone, HomeHeroView){

  var HERO_TIMER = 10000;
  var TRANSITION_TIMER = 0;

  var HomeHerosView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("HomeHeroView:ready", this.onHomeHeroViewReady, this);

	  this.arrHeros = [];
	  this.nCurrHero = 0;
	  this.nLoadingHero = 0;
	  this.bHeroReady = false;
	  this.bWaiting = false;
	  this.bFirstHero = true;      
	  
	  var self = this;
	  // nav btns
	  $('.button', $(this.el)).mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');	  	
	  });
	  $('.button', $(this.el)).click(function(evt){
	    self.bWaiting = true;     
	    self.loadHero(Number($(this).attr('data-slide')));	  	
	  });
    },   
    render: function(){
      var self = this;
      
      var homeHeroView = null, heroModel = null;
      $('.hero', $(this.el)).each(function(index) {
	    homeHeroView = new HomeHeroView({ el: this, pos: index });
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
      	
      	this.arrHeros[this.nCurrHero].hideContent();
  
  		self.onTransitionTimer();
        this.nTransitionTimer = setTimeout(function() {
//          self.onTransitionTimer();
        }, TRANSITION_TIMER);           	      	
      }

	  // still waiting - show loader      
      if (this.bWaiting) {
        $('#tb-loader-overlay').fadeIn();	
      }      
    },
    onTransitionTimer: function(){
      var self = this;

      this.arrHeros[this.nCurrHero].hide();
      this.arrHeros[this.nCurrHero].setZIndex(1);

      this.nCurrHero = this.nLoadingHero;
      
      this.arrHeros[this.nCurrHero].show();      
      this.arrHeros[this.nCurrHero].setZIndex(2);
          	    	
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
