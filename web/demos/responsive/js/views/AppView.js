define([
  'underscore', 
  'backbone',
  'models/TrailMediaModel',
  'views/TrailSliderView',
  'views/TrailMapView'
], function(_, Backbone, TrailMediaModel, TrailSliderView, TrailMapView){

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
	  	  
	  this.bFullMap = false
	  
	  $('#location_map').addClass('mini');
	  $('.toggle-btn').click(function(evt){
	  	switch (self.bFullMap) {
	  	  case true:
	  	    self.bFullMap = false;
	  	    $('#location_map').addClass('mini');
	  	    $('.royalSlider').removeClass('mini');
	  	    self.trailSliderView.render();
//	  	    self.trailMapView.render();
	  	    self.trailMapView.setView(false);
	  	    break;
	  	    
	  	  case false:
	  	    self.bFullMap = true;
	  	    $('#location_map').removeClass('mini');
	  	    $('.royalSlider').addClass('mini');
	  	    self.trailSliderView.render();
//	  	    self.trailMapView.render();
	  	    self.trailMapView.setView(true);	  	    
	  	    break;	  		
	  	}
	  });	  	  
	  	  
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
	  console.log('r:'+$('.location_map').hasClass('mini'));
	  console.log('t:'+$('.location_map').css('float'));
	  if ($('.location_map').css('float') == 'none') {
	  	console.log('MINI');
	  	this.trailMapView.setView(false);
	  }
	  else {
	  	console.log('FULL');
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
      });
      this.trailMapView.renderMarkers();
      this.trailSliderView.render();
      
      this.handleResize();
	},
    onTrailSlideChanged: function(nSlide){
      this.trailMapView.gotoMedia(nSlide);
	},
    onTrailMapMediaMarkerClick: function(mapMediaMarkerView){
      // look up model in collection
      var nMedia = this.mediaCollection.indexOf(mapMediaMarkerView.model);
      
      this.trailSliderView.gotoMedia(nMedia);
    },
	
  });

  return AppView;
});
