define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/TrailsTrailCardView'
], function (_, Backbone, ActivityFeedView, TrailsTrailCardView){

  var TrailsView = Backbone.View.extend({
    initialize: function(){
      this.nPage = 0;
      this.PageSize = 7;
      
      var self = this;
      
	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }
      
	  $('.more_btn').click(function(evt){	
	    evt.stopPropagation();

	    $('.more_btn').hide();
	    $('.tb-loader').show();
	
	    self.nPage++;
	    self.getResults();
	  });      
    },            
    getResults: function(){
      var self = this;

	  var nOffSet = this.nPage * (this.PageSize);
		  
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
	      $('.tb-loader').hide();

          if (data.value.routes.length) {
            var model, trailsTrailCardView;
      	    $.each(data.value.routes, function(key, card) {
	          model = new Backbone.Model(card);    	
      		  trailsTrailCardView = new TrailsTrailCardView({ model: model});
    		  $('#trailCards').append(trailsTrailCardView.render().el);      	  	
      	    });           
          }
		  // do we have any more to get?          
          if (data.value.totalCount > (self.PageSize + nOffSet)) {
	        $('.more_btn').show();
		  }          
        }
      });        
    }    
    
  });

  return TrailsView;
});
