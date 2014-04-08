define([
  'underscore', 
  'backbone',
  'views/ActivityFeedItemView'
], function (_, Backbone, ActivityFeedItemView){

  var ActivityFeedView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#activityFeedViewTemplate').text());        
            
    },            
    render: function(){
      var self = this;
                
      $(this.el).html(this.template());
/*                        
	  $('.show_activity').click(function(evt){
	    $('.more_btn').attr('disabled', false);  
	  	$('.activity_list').css('top', 0);        
	  });	

	  $('.more_btn').click(function(evt){	
	    evt.stopPropagation();
	  
	  	$('.more_btn').attr('disabled', true);  
	  	$('.activity_list').css('top', -243);        
	  });
*/                        
      return this;
    },
    renderItems: function(arrItems){
      var activityItemFeedView = null, elItem, model;
      for (var nItem=0; nItem < arrItems.length; nItem++) {
        model = new Backbone.Model(arrItems[nItem]);
        activityFeedItemView = new ActivityFeedItemView({ model: model });
        elItem = activityFeedItemView.render();
        $(this.el).append(elItem.el);
	  }    	
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
          self.renderItems(data.items);
        }
      });        
    }    
    
  });

  return ActivityFeedView;
});
