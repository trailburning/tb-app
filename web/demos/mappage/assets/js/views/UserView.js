define([
  'underscore', 
  'backbone',
  'models/TrailModel',
  'views/OverlayView',
  'views/TrailmakerDeleteTrailView',
  'views/ProfileMapView',  
  'views/ActivityFeedView'  
], function(_, Backbone, TrailModel, OverlayView, TrailmakerDeleteTrailView, ProfileMapView, ActivityFeedView){

  var AppView = Backbone.View.extend({
    initialize: function(){
      app.dispatcher.on("OverlayView:close", this.onTrailmakerDeleteTrailViewClose, this);
      app.dispatcher.on("TrailmakerDeleteTrailView:proceed", this.onTrailmakerDeleteTrailViewProceed, this);
      app.dispatcher.on("TrailmakerDeleteTrailView:close", this.onTrailmakerDeleteTrailViewClose, this);
    	
      var self = this;
  	  
	  this.trailModel = new TrailModel();
  	  
	  this.elLikeBtn = $('.like_btn', $(this.el));
	  this.elCurrPanel = null;
	
      this.profileMapView = new ProfileMapView({ el: '#profile_map_view' });
      this.profileMapView.render();

	  if (typeof TB_USER_ID != 'undefined') {
      	this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
      	this.activityFeedView.render();
      	this.activityFeedView.getActivity();	  	
	  }

      this.overlayView = new OverlayView({ el: '#tb-overlay-view', model: this.model });
	  $('.btnDeleteTrail').click(function(evt){
	  	// get id
	  	self.elCurrPanel = $(this).closest('.panel');
	  	if (self.elCurrPanel.length) {	    
	  	  self.showDeleteDialog(self.elCurrPanel.attr('data-id'), self.elCurrPanel.attr('data-name'));	  		
	  	}
	  });

	  function updateFollowBtn() {
	    if (self.elLikeBtn.hasClass('pressed-btn-tb')) {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-on')+' '+self.elLikeBtn.attr('data-firstname'));
	    }
	    else {
	  	  $('.btn-label', self.elLikeBtn).text(self.elLikeBtn.attr('data-off')+' '+self.elLikeBtn.attr('data-firstname'));
	    }
	  }

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
	showDeleteDialog: function(nTrailID, strTrailName){	  
	  this.trailModel.set('id', nTrailID);	  
      this.trailModel.set('trail_name', strTrailName);
				
	  $('#tb-content-overlay').height($('#bodyview').height());
      $('#tb-content-overlay').show();
      $('#tb-overlay-view').show();
      this.overlayView.render();

      this.trailmakerDeleteTrailView = new TrailmakerDeleteTrailView({ el: '#overlayContent_view', model: this.trailModel });
	  this.trailmakerDeleteTrailView.render();
            
      $("body").animate({scrollTop:0}, '500', 'swing');      
	},
    onTrailmakerDeleteTrailViewProceed: function(){
      if (this.elCurrPanel) {
        // remove the trail
        this.trailModel.destroy();
	    this.elCurrPanel.remove();    	
      }
	},	
    onTrailmakerDeleteTrailViewClose: function(){
      $('#tb-overlay-view').hide();
      $('#tb-content-overlay').hide();
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
