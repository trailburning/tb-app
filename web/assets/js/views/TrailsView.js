define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/TrailsTrailCardView',
  'views/TrailsTrailEventCardView',  
], function (_, Backbone, ActivityFeedView, TrailsTrailCardView, TrailsTrailEventCardView){

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
            var model, trailsTrailCardView, bEvent;
      	    $.each(data.value.routes, function(key, card) {
      	      bEvent = false;
	          model = new Backbone.Model(card);    	
	          switch (model.get('slug')) {
	          	case '16km':
	          	case '30km':
	          	case '46km':
	          	  bEvent = true;	          	
	          	  model.set('sponsorURL', 'event/ultraks');
	          	  break;	          	  
	          	case 'e16':
	          	case 'e51':
	          	case 'e101':
	          	  bEvent = true;	          	
	          	  model.set('sponsorURL', 'event/eiger');
	          	  break;	          	  
	          	case 'ttm':
	          	  bEvent = true;	          	
	          	  model.set('sponsorURL', 'event/tfor');
	          	  break;	          	  
	          	case 'marathon':
	          	  bEvent = true;	          	
	          	  model.set('sponsorURL', 'event/aom');
	          	  break;	          	  
	          	case 'ultramarathon':
	          	  bEvent = true;	          	
	          	  model.set('sponsorURL', 'event/laugavegur');
	          	  break;	          	  
	          	case 'lantau-vertical-hong-kong':
	          	  bEvent = true;	          	
	          	  model.set('sponsorURL', 'event/lantauvertical');
	          	  break;
	          	case 'heysen-105-south-australia':
	          	  bEvent = true;	          	
	          	  model.set('sponsorURL', 'event/heysen105');
	          	  break;
	          }
	          
	          if (model.get('user').type == 'brand') {
				model.set('sponsorURL', 'profile/' + model.get('user').name);
	          	bEvent = true;
	          }
	          
	          if (bEvent) {
	            trailsTrailCardView = new TrailsTrailEventCardView({ model: model});
	          }
	          else {
	            trailsTrailCardView = new TrailsTrailCardView({ model: model});
	          }	          	          
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
