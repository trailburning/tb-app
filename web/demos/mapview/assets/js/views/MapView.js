TB_RESTAPI_BASEURL = 'http://localhost:8888/trailburning_api';
//TB_RESTAPI_BASEURL = 'http://www.trailburning.com/api';

define([
  'underscore', 
  'backbone',
  'views/TrailsTrailCardView'  
], function(_, Backbone, TrailsTrailCardView){

  var MapView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("TrailCardView:markerclick", this.onTrailCardViewMarkerClick, this);

      var self = this;
      
      this.PageSize = 50;
	  this.nPage = 0;
	  this.currTrailCardView = null;
	        
	  this.collection = new Backbone.Collection();
      
      this.map = L.mapbox.map('map', null, {dragging: true, touchZoom: false, scrollWheelZoom:false, doubleClickZoom:false, boxZoom:false, tap:false, zoomControl:true, zoomAnimation:false, attributionControl:false});
      this.layer_street = L.mapbox.tileLayer('mallbeury.map-omeomj70');
      this.layer_sat = L.mapbox.tileLayer('mallbeury.map-eorpnyp3');      
      this.map.addLayer(this.layer_street);
                          
	  this.getResults();
	  
      $(window).resize(function() {
        self.handleResize();
      });    
	  this.handleResize();
    },
    handleResize: function(){
      var nHeight = 600;
      
      if ($(window).height() > nHeight) {
      	nHeight = $(window).height(); 
      }
      
      $('#bodyview').height(nHeight);
      $('#map').height(nHeight);
    },
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
		  
	  var markers = new L.MarkerClusterGroup({ showCoverageOnHover: false,
    	iconCreateFunction: function(cluster) {
    	  var nSize = 40;
    	  var strClass = 'tb-map-marker small';
    	  if (cluster._childCount > 9) {
    	  	nSize = 50;
    	  	strClass = 'tb-map-marker medium';
    	  }     	  
    	  if (cluster._childCount > 99) {
    	  	nSize = 60;
    	  	strClass = 'tb-map-marker large';
    	  } 
          return new L.DivIcon({ className: strClass, html: '<div class="marker">' + cluster.getChildCount() + '</div>', iconSize: [nSize, nSize] });
    	}
	  });
		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?limit='+this.PageSize+'&offset=' + nOffSet;
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
          if (data.value.routes.length) {
            var model;
      	    $.each(data.value.routes, function(key, card) {      	    	
	          model = new Backbone.Model(card);	          
	          self.collection.add(model);
	          			  
	          trailsTrailCardView = new TrailsTrailCardView({ model: model, mapCluster: markers});
    		  $('#trailCards').append(trailsTrailCardView.render().el);      	  				  
      	    });       
	  	    self.map.addLayer(markers);
	  		self.map.fitBounds(markers.getBounds());            	        
          }
          
		  var $container = $('#trailCards');
		  $container.masonry({
	  	    columnWidth: 280,
	  	    itemSelector: '.panel'
		  });
		  
        }
      });        
    },
    onTrailCardViewMarkerClick: function(trailCardView){
      if (this.currTrailCardView) {
      	this.currTrailCardView.selected(false);
      }    	
      trailCardView.selected(true);
      
      var nY = parseInt($(trailCardView.el).css('top'), 10) + parseInt($(trailCardView.el).css('margin-top'), 10) - 83;

	  $('#cardsview').animate({scrollTop:nY}, 2000);        
	  $('.panel').removeClass('selected');
	  $('.panel[data-id='+trailCardView.model.cid+']').addClass('selected');
	  
	  this.currTrailCardView = trailCardView;      
	}        
    
  });

  return MapView;
});
