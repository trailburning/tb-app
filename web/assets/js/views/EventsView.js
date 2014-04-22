define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/EventsEventCardView'
], function (_, Backbone, ActivityFeedView, EventsEventCardView){

  var EventsView = Backbone.View.extend({
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
		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/events/search?limit='+this.PageSize+'&offset=' + nOffSet;
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
          console.log('success');
          console.log(data);
	      $('.tb-loader').hide();

          if (data.value.events.length) {
            var model, eventsEventCardView;
      	    $.each(data.value.events, function(key, card) {
	          model = new Backbone.Model(card);    	
      		  eventsEventCardView = new EventsEventCardView({ model: model});
    		  $('#trailCards').append(eventsEventCardView.render().el);      	  	
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

  return EventsView;
});
