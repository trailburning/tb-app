define([
  'underscore', 
  'backbone',
  'models/TrailMediaModel',
  'views/TrailSliderView',
  'views/TrailMapView',
  'views/TrailAltitudeView'
], function(_, Backbone, TrailMediaModel, TrailSliderView, TrailMapView, TrailAltitudeView){

  var AppView = Backbone.View.extend({
    initialize: function(){
	  var self = this;
	  
      app.dispatcher.on("TrailSliderView:slidechanged", self.onTrailSlideChanged, this);
	  app.dispatcher.on("TrailMapMediaMarkerView:mediaclick", self.onTrailMapMediaMarkerClick, this);
	  
	  var MediaCollection = Backbone.Collection.extend({
    	comparator: function(item) {
    		// sort by datetime
        	return item.get('tags').datetime;
    	}
	  });
      this.mediaCollection = new MediaCollection();    
      this.mediaModel = new TrailMediaModel();
	  
      this.trailSliderView = new TrailSliderView({ el: '.royalSlider', model: this.model, mediaCollection: this.mediaCollection, mediaModel: this.mediaModel });                  
	  this.trailMapView = new TrailMapView({ el: '#location_map', elCntrls: '#view_map_btns', model: this.model });
	  this.trailAltitudeView = new TrailAltitudeView({ el: '#trail_altitude_view', model: this.model });
	  	  
	  this.bFullMap = false
	  
	  $('#view_btns .map_btn').click(function(evt){
	    self.bFullMap = true;
	  	$('#view_btns .map_btn').hide();
	  	$('#view_btns .photo_btn').show();
	  	    
	  	$('#location_map').removeClass('mini');
	  	$('.royalSlider').addClass('mini');
	  	self.trailSliderView.render();
	  	self.trailMapView.setView(true);	  	    	  	
	  });
	  
	  $('#view_btns .photo_btn').click(function(evt){
  	    self.bFullMap = false;
  	    $('#view_btns .photo_btn').hide();
  	    $('#view_btns .map_btn').show();
  	    
  	    $('#location_map').addClass('mini');
  	    $('.royalSlider').removeClass('mini');
  	    self.trailSliderView.render();
  	    self.trailMapView.setView(false);
	  });
	  
	  $('#location_map').addClass('mini');
	  	  
      $(window).resize(function() {
        self.handleResize();
      });    	  	
	    
      // get trail    
//      this.model.set('id', 534);             
      this.model.set('id', 159);             
      this.model.fetch({
        success: function () {        
          self.mediaModel.url = TB_RESTAPI_BASEURL + '/v1/route/'+self.model.get('id')+'/medias';
          self.mediaModel.fetch({
            success: function () {
              self.handleMedia(self.mediaModel);
            }
          });        
        }      
      });      
	},	
	handleResize: function(){
	  // update map based on how the map is being displayed.
	  if ($('.location_map').css('float') == 'none') {
	  	this.trailMapView.setView(false);
	  }
	  else {
	  	this.trailMapView.setView(true);
	  }
	},
    handleMedia: function(){
      var self = this;

      this.trailMapView.render();
      
      var jsonMedia = this.mediaModel.get('value');
      // add to collection
      $.each(jsonMedia, function(key, media) {
      	var model = new Backbone.Model(media);
        self.mediaCollection.add(model);      
        self.trailMapView.addMedia(model);
        self.trailAltitudeView.addMedia(model);
      });
      
      this.trailAltitudeView.render();
      this.trailMapView.renderMarkers();
      this.trailSliderView.render();
      
	  $('#view_btns .map_btn').show();
      
      this.handleResize();
	},
    onTrailSlideChanged: function(nSlide){
      this.trailMapView.gotoMedia(nSlide);
      this.trailAltitudeView.gotoMedia(nSlide);
	},
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      // look up model in collection
      var nMedia = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      this.trailSliderView.gotoMedia(nMedia);
      this.trailAltitudeView.gotoMedia(nSlide);
    },
	
  });

  return AppView;
});
