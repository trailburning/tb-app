define([
  'underscore', 
  'backbone',
  'models/TrailMediaModel',    
  'views/ActivityFeedView',
  'views/TrailPlayerView', 
  'views/TrailDetailView',
  'views/TrailMapRegionView', 
  'views/TrailWeatherView',
  'views/TrailActivitiesView'
], function(_, Backbone, TrailMediaModel, ActivityFeedView, TrailPlayerView, TrailDetailView, TrailMapRegionView, TrailWeatherView, TrailActivitiesView){

  var TrailView = Backbone.View.extend({
    initialize: function(){
      var self = this;

	  var MediaCollection = Backbone.Collection.extend({
    	comparator: function(item) {
    		// sort by datetime
        	return item.get('tags').datetime;
    	}
	  });
      this.mediaCollection = new MediaCollection();    
      this.mediaModel = new TrailMediaModel();
                                    
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
      this.trailPlayerView = new TrailPlayerView({ el: '#trailplayer', model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });                  
      this.trailActivitiesView = new TrailActivitiesView({ el: '#trailactivities_view', model: this.model, bReadonly: true });
      this.trailDetailView = new TrailDetailView({ el: '.trail_detail_panel', model: this.model });
      this.trailMapRegionView = new TrailMapRegionView({ el: '#trail_location_map', model: this.model });      
      
      this.buildBtns();      
      
      $(window).resize(function() {
        self.handleResize();
      });    
  
      $('#trailplayer').show();
      $('.panel_container').show();
      $('#footerview').show();
            
      // get trail    
      this.model.set('id', this.options.nTrail);             
      this.model.fetch({
        success: function () {        
          self.handleTrail();                      
          self.mediaModel.url = TB_RESTAPI_BASEURL + '/v1/route/'+self.model.get('id')+'/medias';
          self.mediaModel.fetch({
            success: function () {
              self.handleMedia(self.mediaModel);
            }
          });        
        }      
      });      
    },   
    buildBtns: function(){
      var self = this;

	  function updateLikeBtn() {
	    if (self.elLikeBtn.hasClass('pressed-btn-tb')) {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-on'));
	    }
	    else {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-off'));
	    }
	  }

  	  $('.like_btn', $(this.el)).click(function(evt){
  	    if ($(this).hasClass('pressed-btn-tb')) {
      	  $(this).removeClass('pressed-btn-tb');
      	  self.like($(this).attr('data-trailid'), false);
  	      updateLikeBtn();
  	    }
        else {
      	  $(this).addClass('pressed-btn-tb');
      	  self.like($(this).attr('data-trailid'), true);
          updateLikeBtn();
  	    }      	
  	  });
    },
    like: function(nTrail, bFollow){    
      var strMethod = 'like';
      if (!bFollow) {
      	strMethod = 'undolike';
      }
    	
      var strURL = TB_RESTAPI_BASEURL + '/v1/route/'+nTrail+'/' + strMethod;
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        headers: {'Trailburning-User-ID': TB_USER_ID}
      });        
    },        
    handleResize: function(){
      this.trailPlayerView.handleResize();
    },
    handleTrail: function(){
      this.trailPlayerView.render();
      this.trailActivitiesView.render();
      this.trailWeatherView = new TrailWeatherView({ el: '#trail_weather_view', lat: this.model.get('value').route.start[1], lon: this.model.get('value').route.start[0] });
      this.trailWeatherView.render();
      if (this.model.get('value').route.attributes != undefined) {
        $('.activity_panel').show();
      }
      this.trailDetailView.render();
	  this.trailMapRegionView.render();
    },
    handleMedia: function(){
      var self = this;
      
      this.trailPlayerView.handleMedia();
      
      var jsonMedia = this.mediaModel.get('value');
      // add to collection
      $.each(jsonMedia, function(key, media) {
        self.mediaCollection.add(new Backbone.Model(media));      
      });
      
      // keyboard control
      $(document).keydown(function(e){
      	switch (e.keyCode) {
      	  case 13: // toggle fullscreen
            e.preventDefault();
            self.trailPlayerView.toggleFullscreen();
      	    break;
      	}
      });
      this.handleResize();
    }
    
  });

  return TrailView;
});
