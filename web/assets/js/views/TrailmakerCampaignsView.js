define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailmakerCampaignsView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#campaignsViewTemplate').text());
      
      this.campaignsCollection = null;
      this.bRendered = false;        
    },
    getAndRender: function(){
      var self = this;
      
      var strURL = TB_RESTAPI_BASEURL + '/v1/route/' + this.model.id + '/related/campaigns';
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {
//          console.log(data);
          self.campaignsCollection = new Backbone.Collection(data.value.campaigns);
          if (data.value.campaigns.length) {
            self.render();          	
          }      
        }
      });      

	},	    
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
                            
	  var self = this;                            

	  // mla - note we currently only support 1 campaign                            
	  this.campaignsCollection.each(function(campaign) {
  		self.model.set('url', campaign.get('slug'));
  		self.model.set('image', campaign.get('image'));
  		self.model.set('logo', campaign.get('logo'));
  		self.model.set('title', campaign.get('title'));
  		self.model.set('text', campaign.get('text'));
	  });                            
                            
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

	  $(this.el).show();

	  // listen for check
	  // mla - this is only until we extend API to accept campaigns
  	  $('input[type="checkbox"]').click(function(){
        if($(this).prop("checked") == true){                
          window.location.href = "mailto:hello@trailburning.com?subject=" + self.model.get('title') + ' - Please review my trail';
        }
      });
        
      var elImg = $('img', $(this.el));
      var imgLoad = imagesLoaded(elImg);
	  imgLoad.on('always', function(instance) {
	    for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
	      // do we want to scale?
	      if ($(imgLoad.images[i].img).hasClass('scale')) {
	  	    $(imgLoad.images[i].img).addClass('scale_image_ready');	      	
	      }
	   	}	  			   	
        $('.fade_on_load', $(self.el)).addClass('tb-fade-in');
        $('.image_container', $(self.el)).css('opacity', 1);        
	  });      
	  // force resrc update
	  resrc.resrc($('img.resrc', $(this.el)));	        
	  	  
      return this;
    }
    
    
  });

  return TrailmakerCampaignsView;
});
