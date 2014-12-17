define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TwitterView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#twitterViewTemplate').text());        
    },            
    getResults: function(){
      var self = this;

	  this.strTwitterURL = 'https://twitter.com/';
		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/socialmedia';
	  if (this.options.search) {
		strURL += '?term=' + this.options.search;
		this.strTwitterURL += 'search?q=' + this.options.search;
	  }
	  else if (this.options.user) {
	    strURL += '?user=' + this.options.user;
	    this.strTwitterURL += this.options.user;
	  }
	  else {
	  	return;
	  }
		  
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
		  $(self.el).html('');

		  var strTweet = '', strImage = '';
          if (data.value) {
      	    $.each(data.value, function(key, tweet) {
      	    	var bRetweet = false;
      	    	
      	    	if (tweet.text.indexOf('RT') == 0) {
      	    	  bRetweet = true;
      	    	}
      	    	
      	    	if (self.options.bShowRetweets == false && bRetweet) {
      	    	  // no retweets thanks
      	    	}
      	    	else {
      	    	  strImage = '';
      	    	  if (tweet.images.length) {
      	    	    strImage = '<a href="'+tweet.images[0].expanded_url+'" target="_blank"><div class="image_container fade_on_load"><img src="'+tweet.images[0].media_url+'" class="scale" border="0"></div></a>'; 
      	    	  }
        	      strTweet = '<div class="tweet_panel"><a class="icon" href="'+self.strTwitterURL+'" target="_blank"></a><div class="panel"><div class="panel_content "><h5 class="tb">'+tweet.text+strImage+'</h5><div class="details"><time class="timeago" datetime="'+tweet.date+'"></time>&nbsp;&nbsp;<strong>'+tweet.user+'</strong></div></div></div></div>';
      	    	  $(self.el).append(strTweet);
      	    	}
      	    });
      	    
      	    $("time.timeago").timeago();
      	    
      	    self.showImages();           
          }
        }
      });        
    },
    showImages: function(){
	  var self = this;
	  
	  $('.fade_on_load', $(this.el)).imagesLoaded()
  	    .progress( function(instance, image) {
  	  	  $(image.img).addClass('scale_image_ready');
          // update pos
          $(image.img).imageScale();
  	  	
    	  var elContainer = $(image.img).parent();
    	  if (elContainer.hasClass('fade_on_load')) {
            // fade in - delay adding class to ensure image is ready  
            elContainer.addClass('tb-fade-in');
		    var nRnd = 100 * (Math.floor(Math.random() * 6) + 1);
		    setTimeout(function(){
  		  	  elContainer.css('opacity', 1);
		    }, nRnd);
    	  }
    	  if ($(image.img).hasClass('resrc')) {
		    // invoke resrc      
	        resrc.resrc($(image.img));        
    	  }
  	  });        
    }
    
  });

  return TwitterView;
});
