define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/HomeHerosView',
  'views/maps/MapTrailView',
  'views/TwitterView'  
], function(_, Backbone, ActivityFeedView, HomeHerosView, MapTrailView, TwitterView){
  
  var HomeView = Backbone.View.extend({
    initialize: function(){
      var self = this;
        
	  this.collection = new Backbone.Collection();
      this.activityFeedView = null;
        
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
	  	this.activityFeedView.getActivity();
	  }
	  
	  $('.discover_content .scale, .trails_content .scale').imagesLoaded()
  	    .progress( function(instance, image) {
  	  	  $(image.img).addClass('scale_image_ready');
          // update pos
          $(image.img).imageScale();
  	  	
    	  var elContainer = $(image.img).parent();
    	  if (elContainer.hasClass('fade_on_load')) {
            // fade in - delay adding class to ensure image is ready  
            elContainer.addClass('tb-fade-in');
		    var nRnd = 100 * (Math.floor(Math.random() * 6) + 1);
		    setTimeout(function(){
		  	  elContainer.css('opacity', 1);
		    }, nRnd);
    	  }
    	  if ($(image.img).hasClass('resrc')) {
		    // invoke resrc      
	        resrc.resrc($(image.img));        
    	  }    	  
  	  });    
  	
      this.homeHerosView = new HomeHerosView({ el: '#home_header' });
	  this.homeHerosView.render();
	
      this.trailMapView = new MapTrailView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.model });
	  this.trailMapView.render();	  
	
	  var strTwitterUser = "trailburning";
      this.twitterView = new TwitterView({ el: '#twitter_view', model: this.model, user: strTwitterUser, bShowRetweets: true });
      this.twitterView.getResults();            
	
	  this.getResults();
	
      $(window).resize(function() {
        self.handleResize(); 
      });    
      this.handleResize();        
	
  	  // keyboard control
  	  $(document).keydown(function(e){
  	    switch (e.keyCode) {
  	      case 37: // previous hero
  	        self.homeHerosView.prevHero();
  	        break;
  	  	  case 39: // next hero
  	        self.homeHerosView.nextHero();
  	        break;
  	    }
  	  });
    
      $('#footerview').show();
    },
    getResults: function(){
      var self = this;

	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?limit=500&offset=0';
	  	  
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
          var model;
      	  $.each(data.value.routes, function(key, card) {
	    	model = new Backbone.Model(card);
	    	self.trailMapView.addTrail(model);
	    	self.collection.add(model);	    
		  });
		  self.trailMapView.updateTrails();
		  
		  $('#trail_map_view #map_large').show();		  	
	  	  $('#view_map_btns').show();

	  	  self.trailMapView.render();		  
        }
      });        
    },        
    handleResize: function(){
      $("img.scale_image_ready").imageScale();
	}
	
  });

  return HomeView;
});
