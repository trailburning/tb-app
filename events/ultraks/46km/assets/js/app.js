var app = app || {};

var RESTAPI_BASEURL = 'http://trailburning.herokuapp.com/api/';
//var RESTAPI_BASEURL = 'http://localhost:8888/api/';

define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'models/TrailMediaModel',
  'views/TrailMiniMapView',
  'views/TrailMiniSlideView',
  'views/TrailSlideView',  
  'views/TrailMapView',  
  'views/TrailStatsView',  
  'views/TrailAltitudeView',
  'views/TrailWeatherView'
], function(_, Backbone, TrailModel, TrailMediaModel, TrailMiniMapView, TrailMiniSlideView, TrailSlideView, TrailMapView, TrailStatsView, TrailAltitudeView, TrailWeatherView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var MIN_WIDTH = 1200;
  
  var SLIDE_VIEW = 0;
  var MAP_VIEW = 1;

  var HOLD_SLIDE = 8000;
  var TICKLE_TIMER = 5000;

  var SLIDESHOW_INIT = 0;
  var SLIDESHOW_PLAYING = 1;
  var SLIDESHOW_STOPPED = 0;

  var TITLE_OFF = 0;
  var TITLE_ON = 1;
  
  var initialize = function() {
    var self = this;
    
    app.dispatcher.on("TrailMapMediaMarkerView:mediaclick", onTrailMapMediaMarkerClick, this);
    app.dispatcher.on("TrailMediaMarkerView:mediaclick", onTrailMediaMarkerClick, this);
    app.dispatcher.on("TrailSlideView:slideview", onTrailSlideViewSlideView, this);
    
    this.nTrailView = SLIDE_VIEW;
    this.nTitleState = TITLE_OFF;
    this.nSlideShowState = SLIDESHOW_INIT;
    this.nPlayerHeight = 0;
    this.slideTimer = null;
    this.nCurrSlide = -1;
    this.nTickleCount = 0;
    this.nOldTickleCount = 0;    
    this.mediaCollection = new Backbone.Collection();
    
    this.trailModel = new TrailModel();
    this.mediaModel = new TrailMediaModel();

    this.trailStatsView = new TrailStatsView({ el: '#trail_stats_view', model: this.trailModel });
    this.trailAltitudeView = new TrailAltitudeView({ el: '#trail_altitude_view', model: this.trailModel });
    this.trailMiniMapView = new TrailMiniMapView({ el: '#trail_minimap_view', model: this.trailModel });
    this.trailMiniSlideView = new TrailMiniSlideView({ el: '#trail_minislide_view', model: this.trailModel });

    this.trailSlideView = new TrailSlideView({ el: '#trail_slide_view', model: this.mediaModel });
    this.trailMapView = new TrailMapView({ el: '#trail_map_view', elCntrls: '#view_map_btns', model: this.trailModel });
    
    this.trailWeatherView = new TrailWeatherView({ el: '#trail_weather_view', model: this.trailModel });
    
    this.userProfileMap = null;

    bSlideFull = true;
    
    buildBtns();
    updatePlayerSize();
    
    $('#search_form').submit(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });    
    
    var imgLoad = imagesLoaded('.panels .scale');
    imgLoad.on('always', function(instance) {
      for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
        $(imgLoad.images[i].img).addClass('scale_image_ready');
        // update pos
        $(imgLoad.images[i].img).imageScale();
      }
      // fade in - delay adding class to ensure image is ready  
      $('.panels .fade_on_load').addClass('tb-fade-in');
      $('.panels .image_container').css('opacity', 1);
    });
    
    $(window).resize(function() {
      handleResize(); 
    });    

    $(window).mousemove(function(evt) {
      tickle();
    });

    function tickle() {
      self.nTickleCount++;

      showTitle();
    }

    function buildBtns() {
      $('#view_toggle .button').click(function(evt){
        onTrailToggleViewBtnClick();
      });
      $('#view_toggle .button').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
      });

      $('#trail_overlay .overlay_pull').click(function(evt){
        toggleSlide();
      });
      $('#trail_overlay .overlay_pull').mouseover(function(evt){
        $(evt.currentTarget).css('cursor','pointer');      
      });
    }

    function toggleSlide() {
      if (bSlideFull) {
        bSlideFull = false;
        
        $('#trail_views').css('top', -50);
        $('#trailplayer').height(self.nPlayerHeight+50);
        
        $('#trail_overlay').css('top', -218);

        $('#trail_stats_view').css('top', 0);
        $('#trail_altitude_view').css('top', 0);        
        $('#trail_mini_view').css('top', 0);
      }
      else {
        bSlideFull = true;        

        $('#trail_views').css('top', 0);
        $('#trailplayer').height(self.nPlayerHeight);
        
        $('#trail_stats_view').css('top', 40);
        $('#trail_altitude_view').css('top', 60);
        $('#trail_mini_view').css('top', 80);
        
        $('#trail_overlay').css('top', 0);        
      }     
    }

    function startSlideShow() {    
      self.nSlideShowState = SLIDESHOW_PLAYING;
          
      nextSlide();
    }
    
    function stopSlideShow() {    
      self.nSlideShowState = SLIDESHOW_STOPPED;
      
      if (self.slideTimer) {
        clearTimeout(self.slideTimer);
      }
    }    

    function nextSlide(){    
      var nSlide = self.nCurrSlide; 
      if (nSlide < self.mediaCollection.length-1) {
        nSlide++;                               
      }
      else {
        nSlide = 0;
      }
      gotoMedia(nSlide);
    }    

    function gotoMedia(nSlide){
      self.trailMiniSlideView.gotoSlide(nSlide);    
      self.trailSlideView.gotoSlide(nSlide);
      
      self.trailMiniMapView.gotoMedia(nSlide);
      self.trailMapView.gotoMedia(nSlide);
      
      self.trailAltitudeView.gotoMedia(nSlide);
      
      self.nCurrSlide = nSlide;      
    }

    function showTitle() {
      if (self.nTitleState != TITLE_OFF) {
        return;
      }    
      self.nTitleState = TITLE_ON;
      
      $('#trail_info').css('top', 24);       
      $('#trail_info .trail_avatar').css('top', 0);       
      $('#trail_info .trail_title').css('top', 0);       
    }

    function hideTitle() {    
      if (self.nTitleState != TITLE_ON) {
        return;
      }    
      self.nTitleState = TITLE_OFF;
      
      $('#trail_info').css('top', -300);        
      $('#trail_info .trail_avatar').css('top', -300);       
      $('#trail_info .trail_title').css('top', -100);       
    }

    function updatePlayerSize() {
      var nPlayerHeight = 0, nExpandHeight = 50;      
      var elPlayer = $('#trailplayer');

      nPlayerHeight = Math.round(elPlayer.width() / 2);
      // check height fits
      if ((nPlayerHeight+nExpandHeight+elPlayer.position().top) > $(window).height()) {  
        nPlayerHeight = $(window).height() - nExpandHeight - elPlayer.position().top; 
      }
      if (nPlayerHeight < (MIN_WIDTH / 2)) {
        nPlayerHeight = (MIN_WIDTH / 2);
      }
      self.nPlayerHeight = nPlayerHeight;
      
      var nExtendHeight = nExpandHeight * 2;                  
      if (bSlideFull) {
        $('#trailplayer').height(self.nPlayerHeight);            
      }
      else {
        $('#trailplayer').height(self.nPlayerHeight + nExpandHeight);            
      }      
      $('#trail_slide_view').height(self.nPlayerHeight+nExtendHeight);
      $('#trail_map_view').height(self.nPlayerHeight+nExtendHeight);
      $('#trail_map_view .map_container').height(self.nPlayerHeight+nExtendHeight);
    }

    function onTickleTimer(){
//      console.log("onTickleTimer:"+self.nOldTickleCount+' : '+self.nTickleCount);
      if (self.nOldTickleCount == self.nTickleCount) {
        hideTitle();
      }
      self.nOldTickleCount = self.nTickleCount;         
    }

    function onTrailMapMediaMarkerClick(mapMediaMarkerView) {
      // look up model in collcetion
      var nMedia = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      stopSlideShow();
      gotoMedia(nMedia);
    }

    function onTrailMediaMarkerClick(mediaMarkerView) {
      // look up model in collcetion
      var nMedia = this.mediaCollection.indexOf(mediaMarkerView.model);
      
      stopSlideShow();
      gotoMedia(nMedia);
    }

    function onTrailToggleViewBtnClick() {
      switch (self.nTrailView) {
        case MAP_VIEW:
          self.nTrailView = SLIDE_VIEW;
          
          $('#view_toggle .button').addClass('view_map');
          $('#view_toggle .button').removeClass('view_photo');
          
          self.trailMiniSlideView.hide();
          self.trailMiniMapView.show();
          self.trailMiniMapView.render();
          
          self.trailMapView.hide();
          self.trailSlideView.show();
          self.trailSlideView.render();
          break;
          
        case SLIDE_VIEW:
          self.nTrailView = MAP_VIEW;
          
          $('#view_toggle .button').addClass('view_photo');
          $('#view_toggle .button').removeClass('view_map');

          self.trailMiniMapView.hide();
          self.trailMiniSlideView.show();
          self.trailMiniSlideView.render();
          
          self.trailSlideView.hide();
          self.trailMapView.show();
          self.trailMapView.render();
          break;
      }
      handleResize();      
    }

    function onTrailSlideViewSlideView() {
      // start timer
      if (self.slideTimer) {
        clearTimeout(self.slideTimer);
      }
      
      if (self.nSlideShowState == SLIDESHOW_PLAYING) {
        self.slideTimer = setTimeout(function() {
          onShowNextSlide();
        }, HOLD_SLIDE);
      }
    }    

    function onShowNextSlide() {
      nextSlide();          
    }
    
    function handleResize() {
      updatePlayerSize();
      
      switch (self.nTrailView) {
        case SLIDE_VIEW:
          self.trailSlideView.render($('#appview').width());
          self.trailMiniSlideView.render();
          break;
           
        case MAP_VIEW:
          $('#trailplayer .map_container').width($('#appview').width());
          self.trailMiniSlideView.render();
          self.trailMapView.render();
          break;
      }      
      self.trailStatsView.render();
      self.trailAltitudeView.render();
      
      $("img.scale_image_ready").imageScale();
    }        
      
    function handleTrail() {      
      $('#trailplayer').addClass('tb-size');
      
      $('#trail_views').addClass('tb-move');
      $('#trail_slide_view').addClass('tb-size');
      
      $('#trail_overlay').addClass('tb-move');
      $('#trail_info').addClass('tb-move');
      $('#trail_info .trail_avatar').addClass('tb-move');       
      $('#trail_info .trail_title').addClass('tb-move');
            
      $('#trail_stats_view').addClass('tb-move');
      $('#trail_altitude_view').addClass('tb-move');            
      $('#trail_mini_view').addClass('tb-move');      
      
      var self = this;          
      this.nTickleTimer = setInterval(function() {
        onTickleTimer();
      }, TICKLE_TIMER);      
    }
    
    function handleMedia() {
      var jsonMedia = self.mediaModel.get('value');
      $.each(jsonMedia, function(key, media) {
        var mediaModel = new Backbone.Model(media);        
        self.mediaCollection.add(mediaModel);      
                
        self.trailMiniMapView.addMedia(mediaModel);
        self.trailMapView.addMedia(mediaModel);
        
        self.trailMiniSlideView.addMedia(mediaModel);
        self.trailSlideView.addMedia(mediaModel);
        
        self.trailAltitudeView.addMedia(mediaModel);
        self.trailWeatherView.render();
      });
      self.trailAltitudeView.renderMarkers();
      self.trailMiniMapView.renderMarkers();          
      self.trailMapView.renderMarkers();
          
      handleResize();      
      startSlideShow();
    }
    
    // get trail    
    this.trailModel.set('id', nTrail);             
    this.trailModel.fetch({
      success: function () {
        self.trailMiniMapView.render();
        self.trailMapView.render();

        handleTrail();

        self.mediaModel.url = RESTAPI_BASEURL + 'v1/route/'+self.trailModel.get('id')+'/medias';
        self.mediaModel.fetch({
          success: function () {
            handleMedia(self.mediaModel);
            toggleSlide();
            tickle();      
          }
        });        
      }      
    });    
  };
    
  return { 
    initialize: initialize
  };   
});  
