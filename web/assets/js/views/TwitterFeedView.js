define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TwitterFeedView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#twitterViewTemplate').text());        
    },            
    getResults: function(){
      var self = this;
		  
      var strURL = '/server/tweet.php?screenname=' + this.options.screenname + '&limit=3';      
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

      	    $.each(data, function(key, tweet) {
  	    	  strImage = '';
  	    	  if (tweet.entities.media) {
  	    	    strImage = '<a href="'+tweet.entities.media[0].expanded_url+'" target="_blank"><div class="image_container fade_on_load"><img src="'+tweet.entities.media[0].media_url+'" class="scale" border="0"></div></a>'; 
  	    	  }
    	      strTweet = '<div class="tweet_panel"><a class="icon" href="" target="_blank"></a><div class="panel"><div class="panel_content "><h5 class="tb">'+tweet.formattedText+strImage+'</h5><div class="details"><time class="timeago" datetime="'+tweet.created_at+'"></time>&nbsp;&nbsp;<strong>'+tweet.user.name+'</strong></div></div></div></div>';
  	    	  $(self.el).append(strTweet);
      	    });
      	    
      	    $("time.timeago").timeago();
      	    
      	    self.showImages();           
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

  return TwitterFeedView;
});
