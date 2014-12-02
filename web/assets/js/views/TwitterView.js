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
		  
	  var strURL = TB_RESTAPI_BASEURL + '/v1/socialmedia?term=trailburning';
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

		  var strTweet;
          if (data.value) {
      	    $.each(data.value, function(key, tweet) {
//      	    	console.log(tweet);      	    	
      	    	strTweet = '<div class="tweet_panel"><div class="panel"><div class="content"><h4 class="tb">'+tweet.text+'</h4></div></div></div>';
      	    	if (key == 0) {
				  strTweet = '<div class="tweet_panel"><div class="panel"><div class="content"><h4 class="tb">'+tweet.text+'<div class="image_container fade_on_load"><img src="https://pbs.twimg.com/media/ByPGdeIIUAA-ClX.jpg:large" class="scale" border="0"></div></h4></div></div></div>';      	    		
      	    	}
      	    	$(self.el).append(strTweet);
      	    });
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
