define([
  'underscore', 
  'backbone',
  'models/TrailMediaModel',    
  'views/ActivityFeedView',
  'views/TrailPlayerView',  
  'views/TrailWeatherView',
  'views/TrailActivitiesView'
], function(_, Backbone, TrailMediaModel, ActivityFeedView, TrailPlayerView, TrailWeatherView, TrailActivitiesView){

  var TICKLE_TIMER = 5000;
  
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
      
      this.buildBtns();      
      this.trailPlayerView.updatePlayerHeight();
      
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
          
          self.trailPlayerView.render();
            
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
        headers: {'Trailburning-User-ID': TB_USER_ID},
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
        }
      });        
    },        
    handleResize: function(){
      this.trailPlayerView.handleResize();
    },
    handleTrail: function(){      
      // render activities
      this.trailActivitiesView.render();
      // render weather
      this.trailWeatherView = new TrailWeatherView({ el: '#trail_weather_view', lat: this.model.get('value').route.start[1], lon: this.model.get('value').route.start[0] });
      this.trailWeatherView.render();
      if (this.model.get('value').route.attributes != undefined) {
        $('.activity_panel').show();
      }
      
      var self = this;          
      this.nTickleTimer = setInterval(function() {
        self.onTickleTimer();
      }, TICKLE_TIMER);     
      
      $(window).mousemove(function(evt) {
        self.tickle();
      });
      
      var jsonRoute = this.model.get('value').route;
      var elTrailLength = $('.trail_detail_panel .length .marker');
      if (elTrailLength.length) {
        elTrailLength.html(Math.ceil(jsonRoute.length/1000));
      }
        
      var elTrailTerrain = $('.trail_detail_panel .ascent .marker');
      if (elTrailTerrain.length) {
        elTrailTerrain.html(formatAltitude(Math.floor(jsonRoute.tags.ascent)));
      }

      var elTrailTerrain = $('.trail_detail_panel .descent .marker');
      if (elTrailTerrain.length) {
        elTrailTerrain.html(formatAltitude(Math.floor(jsonRoute.tags.descent)));
      }
      
      var jsonPoint = this.model.get('value').route.route_points[0]; 
      var map = L.mapbox.map('trail_location_map', 'mallbeury.map-kply0zpa', {dragging: false, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:false, zoomAnimation:false, attributionControl:false});
      var LocationIcon = L.Icon.extend({
          options: {
              iconSize:     [36, 47],
              iconAnchor:   [16, 44],
              popupAnchor:  [16, 44]
          }
      });      
      
      function onClick(evt) {
      	window.location = $('#trail_location_map').attr('data-url'); 
      }
      
      var startIcon = new LocationIcon({iconUrl: 'http://assets.trailburning.com/images/icons/location.png'});
      L.marker([jsonPoint.coords[1], jsonPoint.coords[0]], {icon: startIcon}).on('click', onClick).addTo(map);      

      var latlng = new L.LatLng(jsonPoint.coords[1], jsonPoint.coords[0]);
      map.setView(latlng, 12);
    },
    handleMedia: function(){
      var self = this;
      
      this.trailPlayerView.handleMedia();
      
      var jsonMedia = this.mediaModel.get('value');
      // add to collection
      $.each(jsonMedia, function(key, media) {
        self.mediaCollection.add(new Backbone.Model(media));      
      });

      this.handleResize();
      
      // keyboard control
      $(document).keydown(function(e){
      	switch (e.keyCode) {
      	  case 27: // close player
//            e.preventDefault();
//            self.trailPlayerView.hidePlayer();
      	    break;
      	  case 13: // toggle overlay
            e.preventDefault();
            self.trailPlayerView.togglePlayer();
      	    break;
      	  case 32: // toggle slideshow
          	e.preventDefault();
          	self.trailPlayerView.toggleSlideshow();      	  
      	    break;
      	  case 37: // previos slide
			self.trailPlayerView.showPrevSlide();      	  
      	    break;
      	  case 39: // next slide
			self.trailPlayerView.showNextSlide();      	  
      	    break;
      	  case 86: // toggle view
			self.trailPlayerView.toggleView();      	  
      	    break;
      	}
      });
    },
    tickle: function(){
      this.nTickleCount++;
    },
    onTickleTimer: function(){
      return;
    	
//      console.log("onTickleTimer:"+this.nOldTickleCount+' : '+this.nTickleCount);
      if (this.nOldTickleCount == this.nTickleCount) {
        this.hideDetailOverlay();
      }
      this.nOldTickleCount = this.nTickleCount;         
    }
    
  });

  return TrailView;
});
