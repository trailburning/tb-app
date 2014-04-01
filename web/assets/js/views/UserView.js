define([
  'underscore', 
  'backbone',
  'views/ProfileMapView'  
], function(_, Backbone, ProfileMapView){

  var AppView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      this.profileMapView = new ProfileMapView({ el: '#profile_map_view' });
      this.profileMapView.render();

  	  $('.like_btn', $(this.el)).click(function(evt){
  	    if ($(this).hasClass('pressed-btn-tb')) {
		  $('.btn-label', $(this)).text($(this).attr('data-off')+' '+$(this).attr('data-firstname'));
      	  $(this).removeClass('pressed-btn-tb');
      	  self.follow($(this).attr('data-userid'), false);
  	    }
        else {
  	  	  $('.btn-label', $(this)).text($(this).attr('data-on')+' '+$(this).attr('data-firstname'));
      	  $(this).addClass('pressed-btn-tb');
      	  self.follow($(this).attr('data-userid'), true);
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
