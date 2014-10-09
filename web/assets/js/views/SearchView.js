define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var SearchView = Backbone.View.extend({
    initialize: function(){
      var self = this;

      var cache = {};
    
	  $('.form-search', $(this.el)).submit(function(evt) {
  	    evt.preventDefault();
	  });
	
	  // setup autosuggest
      $('.searchBox', $(this.el)).autocomplete({
        minLength: 2,
        delay: 0,
        select: function(event, ui)  {
        	window.location = ui.item.url;
        },
        source: function(request, response ) {
            var term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }
            var url = TB_RESTAPI_BASEURL + '/v1/search/suggest?q=' + term;
            $.getJSON(url, request, function( data, status, xhr ) {
                var suggestions = data.hits.hits;
                cache[term] = suggestions;
                response(suggestions);
            });
        }    
	  });
	
      $('.searchBox', $(this.el)).data('ui-autocomplete')._resizeMenu = function() {
    	this.menu.element.outerWidth(300);
      };
      
      $('.searchBox', $(this.el)).data('ui-autocomplete')._renderItem = function(ul, item) {
    	var strItem = "", strURL = TB_BASEURL;
    	var text = item._source.suggest_engram;
    	
        if (item.highlight) {
            if (item.highlight.suggest_engram_full) {
                text = item.highlight.suggest_engram_full;
            } else if(item.highlight.suggest_engram_part) {
                text = item.highlight.suggest_engram_part;
            } else {
                text = item.highlight.suggest_engram;
            }
        }
    	
    	switch (item._type) {
    	  case 'user_profile':
    	    strURL += '/profile/' + item._source.name;
    	    strItem = '<a href="' + strURL + '" class="clearfix"><div class="type"><div class="tb-avatar tb-avatar-search"><div class="photo"><img src="'+item._source.avatar+'"></div></div></div><div class="match">' + text + '<br/>Discover ' + item._source.first_name + '\'s trails.</div></a>';
    	    break;
    	  case 'event':
    	    strURL += '/event/' + item._source.slug
    	    strItem = '<a href="' + strURL + '" class="clearfix"><div class="type"><div class="icon_container"><div class="icon event"></div></div></div><div class="match">' + text + '</div></a>';
    	    break;
    	  case 'editorial':
    	    strURL += '/editorial/' + item._source.slug
    	    strItem = '<a href="' + strURL + '" class="clearfix"><div class="type"><div class="icon_container"><div class="icon editorial"></div></div></div><div class="match">' + text + '</div></a>';
    	    break;
    	  default:
    	    strURL += '/trail/' + item._source.slug
    	    strItem = '<a href="' + strURL + '" class="clearfix"><div class="type"><div class="icon_container"><div class="icon trailcard"></div></div></div><div class="match">' + text + '</div></a>';
    	    break;
    	}

    	item.url = strURL;

        return $('<li>')
            .append(strItem)
            .appendTo(ul);
      };      
    }
  });

  return SearchView;
});
