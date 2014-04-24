define([
  'underscore', 
  'backbone',
  'views/ActivityFeedItemView'
], function (_, Backbone, ActivityFeedItemView){

  var ActivityFeedView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#activityFeedViewTemplate').text());        
            
	  this.bActivityViewed = false;
	  this.arrActivityItems = [];
	  this.nPageSize = 3;
	  this.nCurrPage = 0;            
    },            
    render: function(){
      var self = this;
                
      $(this.el).html(this.template());
                        
	  $('.show_activity').click(function(evt){
	  	self.nCurrPage = 0;
	  	$('.activity_list').css('top', 0);
        
	    if (self.bActivityViewed) {
	      // remove unseen flags
	      for (var nItem = 0; nItem < self.arrActivityItems.length; nItem++) {
		    self.arrActivityItems[nItem].setSeen(true);	
	      }
	    }
        // update seen activity
        self.updateActivityViewed();
        self.checkIfMoreItems();
	  });	

	  $('.more_btn').click(function(evt){	
	    evt.stopPropagation();
	  	  
	  	self.scrollItems();
	  });
                        
      return this;
    },
    renderItems: function(jsonItems){
      var activityItemFeedView = null, elItem, model;
      for (var nItem=0; nItem < jsonItems.items.length; nItem++) {
        model = new Backbone.Model(jsonItems.items[nItem]);
        activityFeedItemView = new ActivityFeedItemView({ model: model });
        this.arrActivityItems.push(activityFeedItemView);
        elItem = activityFeedItemView.render();
        $(this.el).append(elItem.el);
	  }    	
	  this.checkIfMoreItems();
    },
    scrollItems: function(){
      if (!$('.more_btn').attr('disabled')) {
      	this.nCurrPage++;
      	var nY = this.nCurrPage * $('.activity_list_container').height();
	  	$('.activity_list').css('top', -nY);              	
        this.checkIfMoreItems();    		
      }
    },
    checkIfMoreItems: function(){
      var bRet = false;
      
      var nViewItems = (this.nCurrPage * this.nPageSize) + this.nPageSize;
      if (this.arrActivityItems.length > nViewItems) {
      	bRet = true;      	
	    $('.more_btn').attr('disabled', false);  
      }
      else {
	    $('.more_btn').attr('disabled', true);        	      	
      }
      return bRet;
    },
    getActivity: function(){
      var self = this;
      
	  var strURL = TB_RESTAPI_BASEURL + '/v1/activity/feed';
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        headers: {'Trailburning-User-ID': TB_USER_ID},
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
          self.renderItems(data);
        }
      });        
    },
    updateActivityViewed: function(){
      this.bActivityViewed = true;
      
      var self = this;
      
	  $('.profile .activity').hide();
      
	  var strURL = TB_RESTAPI_BASEURL + '/v1/user/activity/viewed';
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
    }    
    
  });

  return ActivityFeedView;
});
