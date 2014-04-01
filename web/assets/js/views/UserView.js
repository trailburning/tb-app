define([
  'underscore', 
  'backbone',
  'views/ProfileMapView'  
], function(_, Backbone, ProfileMapView){

  var AppView = Backbone.View.extend({
    initialize: function(){
      var self = this;

	  this.elLikeBtn = $('.like_btn', $(this.el));
	
      this.profileMapView = new ProfileMapView({ el: '#profile_map_view' });
      this.profileMapView.render();

	  function updateFollowBtn() {
	    if (self.elLikeBtn.hasClass('pressed-btn-tb')) {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-on')+' '+self.elLikeBtn.attr('data-firstname'));
	    }
	    else {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-off')+' '+self.elLikeBtn.attr('data-firstname'));
	    }
	  }
	  // check initial state
	  updateFollowBtn();


  	  $('.like_btn', $(this.el)).click(function(evt){
  	    if ($(this).hasClass('pressed-btn-tb')) {
      	  $(this).removeClass('pressed-btn-tb');
      	  self.follow($(this).attr('data-userid'), false);
  	      updateFollowBtn();
  	    }
        else {
      	  $(this).addClass('pressed-btn-tb');
      	  self.follow($(this).attr('data-userid'), true);
          updateFollowBtn();
  	    }      	
  	  });
    },
    follow: function(nUser, bFollow){    
      var strMethod = 'follow';
      if (!bFollow) {
      	strMethod = 'unfollow';
      }
    	
      var strURL = TB_RESTAPI_BASEURL + '/v1/user/'+nUser+'/' + strMethod;      
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

  return AppView;
});
