define([
  'underscore', 
  'backbone',
  'views/TrailsTrailCardView'
], function (_, Backbone, TrailsTrailCardView){

  var TrailsView = Backbone.View.extend({
    initialize: function(){
      this.nPage = 0;
    },            
    getResults: function(){
      var self = this;
      
	  var strURL = TB_RESTAPI_BASEURL + '/v1/routes/search?limit=10&offset=' + this.nPage;
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
          
          var model, trailsTrailCardView;
      	  $.each(data.value.routes, function(key, card) {
	        model = new Backbone.Model(card);    	
      		trailsTrailCardView = new TrailsTrailCardView({ model: model});
    		$('#trailCards').append(trailsTrailCardView.render().el);      	  	
      	  });           
        }
      });        
    }    
    
  });

  return TrailsView;
});
