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
