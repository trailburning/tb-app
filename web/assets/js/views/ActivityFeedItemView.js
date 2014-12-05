define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var ActivityFeedItemView = Backbone.View.extend({
  	tagName: "li",
  	className: "clearfix",
    initialize: function(){
      this.template = _.template($('#activityFeedItemViewTemplate').text());        
            
    },            
    render: function(){
      var self = this, bAvatar = true;;
                            
      switch (this.model.get('verb')) {
		case 'tb_welcome':
		  this.model.set('displayPublished', 'Now');
		  this.model.set('displaySeen', 'old');
      	  this.model.set('activityURL', '/tour');
      	  this.model.set('actorAvatarURL', 'http://assets.trailburning.com/images/profile/trailburning/avatar.jpg');
      	  
      	  this.model.set('actorDisplayName', 'Trailburning');
      	  this.model.set('preVerbDisplayName', 'welcomes you to a');
      	  this.model.set('verbDisplayName', 'new trail experience');
      	  this.model.set('subjectDisplayName', '');
		  break;

		default:
      	  this.model.set('actorAvatarURL', this.model.get('actor').image.url);
      	  this.model.set('actorDisplayName', this.model.get('actor').displayName);
      
	  	  var dtCreated = new Date(this.model.get('published'));
	  	  var options = {weekday: "long", year: "numeric", month: "long", day: "numeric"};
      	  this.model.set('displayPublished', dtCreated.toLocaleDateString("en-GB", options));
	  	  if (this.model.get('seen')) {
      		this.model.set('displaySeen', 'old');	  	
	  	  }
	  	  else {
      		this.model.set('displaySeen', 'new');	  	
	  	  }

      	  switch (this.model.get('verb')) {
			case 'accept':	  
			  bAvatar = false;
	      	  this.model.set('actorAvatarURL', 'http://assets.trailburning.com/images' + this.model.get('target').url + '/icon.png');
	      	  this.model.set('activityURL', this.model.get('object').url);
	      	  this.model.set('actorDisplayName', this.model.get('target').displayName);
	      	  this.model.set('preVerbDisplayName', 'has');
	      	  this.model.set('verbDisplayName', 'accepted');
	      	  this.model.set('subjectDisplayName', ' a new trail: ' + this.model.get('object').displayName);			  
			  break;
      	  	
      		case 'follow':      	      		
      		  switch (this.model.get('object').objectType) {
      		  	case 'campaign':
      		  	  this.model.set('subjectDisplayName', 'your campaign: ' + this.model.get('object').displayName);
      		  	  break;

      		  	default:
      		  	  this.model.set('subjectDisplayName', 'your trails');
      		  	  break;
      		  }      		
      	  	  this.model.set('activityURL', this.model.get('actor').url);
      	  	  this.model.set('preVerbDisplayName', 'started');
      	  	  this.model.set('verbDisplayName', 'following');      	      	  	        	
      	  	  break;
      		case 'publish':      	      	
      	  	  this.model.set('activityURL', this.model.get('object').url);
      	  	  this.model.set('preVerbDisplayName', 'has');
      	  	  this.model.set('verbDisplayName', 'published');      	
      	  	  this.model.set('subjectDisplayName', ' a new trail: ' + this.model.get('object').displayName);      	
      	  	  break;
      		case 'like':      	      	
      	  	  this.model.set('activityURL', this.model.get('actor').url);
      	  	  this.model.set('preVerbDisplayName', 'likes your trail:');
      	  	  this.model.set('verbDisplayName', '');      	
      	  	  this.model.set('subjectDisplayName', this.model.get('object').displayName);      	
      	  	  break;
      		case 'register':      	      	
	      	  this.model.set('activityURL', '/tour');
	      	  this.model.set('actorAvatarURL', 'http://assets.trailburning.com/images/profile/trailburning/avatar.jpg');
	      	  
	      	  this.model.set('actorDisplayName', 'Trailburning');
	      	  this.model.set('preVerbDisplayName', 'welcomes you to a');
	      	  this.model.set('verbDisplayName', 'new trail experience');
	      	  this.model.set('subjectDisplayName', '');
      	  	  break;
      		default:      	
      	  	  break;
      	  }       
      	  break;
      }
      
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
                      
	  if (!bAvatar) {
	  	$('.tb-avatar', this.el).hide();
	  	$('.logo', this.el).show();
	  } 
	                         
      return this;
    },
    setSeen: function(bSeen){
	  var elSeen = $('.seen', this.el);    	
    	
      this.model.set('seen', bSeen);
	  if (this.model.get('seen')) {
      	this.model.set('displaySeen', 'old');
      	elSeen.removeClass('new');	  	
	  }
	  else {
      	this.model.set('displaySeen', 'new');	  	
      	elSeen.removeClass('old');	  	
	  }
	  elSeen.addClass(this.model.get('displaySeen'));
	}
	    
  });

  return ActivityFeedItemView;
});
