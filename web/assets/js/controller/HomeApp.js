var app = app || {};

define([
  'underscore', 
  'backbone',
  'views/ActivityFeedView',
  'views/HomeHerosView'      
], function(_, Backbone, ActivityFeedView, HomeHerosView){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        
    
    var imgLoad1 = imagesLoaded('.discover_content .scale');
	imgLoad1.on('always', function(instance) {
      for ( var i = 0, len = imgLoad1.images.length; i < len; i++ ) {
        $(imgLoad1.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $('.discover_content img.scale_image_ready').imageScale();
      // fade in - delay adding class to ensure image is ready  
      $('.discover_content .fade_on_load').addClass('tb-fade-in');
      $('.discover_content .image_container').css('opacity', 1);
      // force update to fix blurry bug
	  resrc.resrcAll();
	});
        
    var imgLoad2 = imagesLoaded('.trails_content .scale');
	imgLoad2.on('always', function(instance) {
      for ( var i = 0, len = imgLoad2.images.length; i < len; i++ ) {
        $(imgLoad2.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $('.trails_content img.scale_image_ready').imageScale();
      // fade in - delay adding class to ensure image is ready  
      $('.trails_content .fade_on_load').addClass('tb-fade-in');
      $('.trails_content .image_container').css('opacity', 1);
      // force update to fix blurry bug
	  resrc.resrcAll();
	});        
        
    if (typeof TB_USER_ID != 'undefined') {
  	  this.activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
  	  this.activityFeedView.render();
  	  this.activityFeedView.getActivity();	  	
    }
        
    this.homeHerosView = new HomeHerosView({ el: '#home_header' });
	this.homeHerosView.render();

	// setup autosuggest
    var cache = {};
    var client = new $.es.Client({    
        hosts: 'e7p15amb:4yexy8z21pg5eee0@boxwood-7916136.eu-west-1.bonsai.io'
    });
    
//    $('#q').autocomplete({  	
    $('#searchBox').autocomplete({
        minLength: 2,
        delay: 0,
        source: function(request, response ) {
            var term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }
            
            client.search({
                index: 'trailburning',
                body: {
                    query: {
                        match: {
                          suggest_nge: term
                        }
                    }
                }
            }).then(function (resp) {
                var suggestions = resp.hits.hits;
                cache[term] = suggestions;
                response(suggestions); 
            }, function (err) {
                console.log(err.message);
            });
        }
    });
    
    $('#searchBox').data('ui-autocomplete')._resizeMenu = function() {
    	this.menu.element.outerWidth(300);
    };
    $('#searchBox').data('ui-autocomplete')._renderItem = function(ul, item) {
    	var strItem = "";    	
    	switch (item._type) {
    	  case 'user_profile':
    	    strItem = '<a href="profile/' + item._source.name + '" class="clearfix"><div class="match">' + item._source.suggest_text + '</div><div class="type"><div class="tb-avatar"><div class="photo"><img src="'+item._source.avatar+'"></div></div></div></a>';
    	    break;
    	  default:
    	    strItem = '<a href="trail/' + item._source.slug + '" class="clearfix">' + item._source.suggest_text + '</a>';
    	    break;
    	}
    	
		console.log(item);
    	
        return $('<li>')
            .append(strItem)
            .appendTo(ul);
    };

  	// keyboard control
  	$(document).keydown(function(e){
  	  switch (e.keyCode) {
  	    case 37: // previous hero
  	      self.homeHerosView.prevHero();
  	      break;
  	  	case 39: // next hero
  	      self.homeHerosView.nextHero();
  	      break;
  	  }
  	});
      
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
    
    $('#footerview').show();
  };
    
  return { 
    initialize: initialize
  };   
});  

