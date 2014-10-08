define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/CampaignPlayerView',  
  'views/TrailWeatherView'
], function(_, Backbone, ActivityFeedView, CampaignPlayerView, TrailWeatherView){
  
  var CampaignView = Backbone.View.extend({
    initialize: function(){
      var self = this;

	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
      this.playerView = new CampaignPlayerView({ el: '#trailplayer', model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });            
      this.weatherView = new TrailWeatherView({ el: '#trail_weather_view', model: this.model });
      
      this.playerView.updatePlayerHeight();
      
      $(window).resize(function() {
        self.handleResize();
      });    
  
      $('#campaignplayer').show();
      $('.panel_container').show();
      $('#footerview').show();
      
  	  this.playerView.render();
//      this.weatherView.render();
      
      this.playerView.handleMedia();
      this.handleResize();
      
      // keyboard control
      $(document).keydown(function(e){
      	switch (e.keyCode) {
      	  case 13: // toggle overlay
            e.preventDefault();
            self.playerView.togglePlayer();
      	    break;
      	  case 86: // toggle view
			self.playerView.toggleView();      	  
      	    break;      	    
      	}
      });      
    },   
    handleResize: function(){
      this.playerView.handleResize();
    }
    
  });

  return CampaignView;
});
