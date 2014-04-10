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
      var self = this;
                      
      this.model.set('actorURL', this.model.get('actor').url);
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
      	case 'follow':      	
      	  this.model.set('preVerbDisplayName', 'started');
      	  this.model.set('verbDisplayName', 'following');      	
      	  this.model.set('subjectDisplayName', 'your trails');      	
      	  break;
      	case 'publish':      	
      	  this.model.set('preVerbDisplayName', 'has');
      	  this.model.set('verbDisplayName', 'published a new trail:');      	
      	  this.model.set('subjectDisplayName', 'TRAIL NAME');      	
      	  break;
      	default:      	
      	  break;
      }
      
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));
                        
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
