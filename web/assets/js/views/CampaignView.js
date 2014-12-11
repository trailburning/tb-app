define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/CampaignPlayerView',  
  'views/TwitterView',  
  'views/TrailWeatherView'
], function(_, Backbone, ActivityFeedView, CampaignPlayerView, TwitterView, TrailWeatherView){
  
  var CampaignView = Backbone.View.extend({
    initialize: function(){
      var self = this;

	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
      this.playerView = new CampaignPlayerView({ el: '#trailplayer', model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });
      var fLat, fLng;
      if (CAMPAIGN_TITLE == '6amCLUB') {
      	fLat = 0;
      	fLng = 0;
      }
      if (CAMPAIGN_TITLE == 'Mt Buller') {
      	fLat = -37.132552;
      	fLng = 146.454196;
      }
      if (CAMPAIGN_TITLE == 'London') {
      	fLat = 51.507351;
      	fLng = -0.127758;
      }
 
      this.twitterView = new TwitterView({ el: '#twitter_view', model: this.model, search: CAMPAIGN_TWITTER_QUERY });
      this.twitterView.getResults();            
      this.weatherView = new TrailWeatherView({ el: '#trail_weather_view', lat: fLat, lon: fLng });
	  this.elLikeBtn = $('.like_btn', $(this.el));
      
      this.playerView.updatePlayerHeight();
      
      $(window).resize(function() {
        self.handleResize();
      });    
  
      $('#campaignplayer').show();
      $('.panel_container').show();
      
  	  this.playerView.render();
      this.weatherView.render();

      $('#content_view').show();
      $('#footerview').show();
      
      this.playerView.handleMedia();
      
	  function updateFollowBtn() {
	    if (self.elLikeBtn.hasClass('pressed-btn-tb')) {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-on')+' '+self.elLikeBtn.attr('data-campaignname'));
	    }
	    else {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-off')+' '+self.elLikeBtn.attr('data-campaignname'));
	    }
	  }

  	  $('.like_btn', $(this.el)).click(function(evt){
  	    if ($(this).hasClass('pressed-btn-tb')) {
      	  $(this).removeClass('pressed-btn-tb');
      	  self.follow($(this).attr('data-campaignid'), false);
  	      updateFollowBtn();
  	    }
        else {
      	  $(this).addClass('pressed-btn-tb');
      	  self.follow($(this).attr('data-campaignid'), true);
          updateFollowBtn();
  	    }      	
  	  });
      
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
    },
    follow: function(nCampaign, bFollow){    
      var strMethod = 'follow';
      if (!bFollow) {
      	strMethod = 'unfollow';
      }
    	
      var strURL = TB_RESTAPI_BASEURL + '/v1/campaign/'+nCampaign+'/' + strMethod;
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        headers: {'Trailburning-User-ID': TB_USER_ID},
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
        }
      });        
    }    
    
  });

  return CampaignView;
});
