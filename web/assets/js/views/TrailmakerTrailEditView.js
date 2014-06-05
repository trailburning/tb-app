define([
  'underscore', 
  'backbone',
  'views/OverlayView',  
  'views/TrailUploadPhotoView',
  'views/TrailUploadPhotoProgressView',
  'views/TrailUploadPhotoErrorView',
  'views/TrailSlideshowView',
  'views/TrailActivitiesView',  
], function(_, Backbone, OverlayView, TrailUploadPhotoView, TrailUploadPhotoProgressView, TrailUploadPhotoErrorView, TrailSlideshowView, TrailActivitiesView){

  var STATE_UPLOAD = 0;

  var TrailmakerTrailEditView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#stepRouteEditViewTemplate').text());        
      
      app.dispatcher.on("TrailUploadPhotoView:upload", this.onTrailUploadPhotoViewUpload, this);      
      app.dispatcher.on("TrailUploadPhotoView:uploaded", this.onTrailUploadPhotoViewUploaded, this);
      app.dispatcher.on("TrailUploadPhotoView:uploadProgress", this.onTrailUploadPhotoViewUploadProgress, this);
      app.dispatcher.on("TrailUploadPhotoView:error", this.onTrailUploadPhotoViewError, this);      
      app.dispatcher.on("TrailUploadPhotoErrorView:closeclick", this.onTrailUploadPhotoErrorViewCloseClick, this);      


      app.dispatcher.on("TrailMapView:mediaclick", this.onTrailMapViewMediaClick, this);
      app.dispatcher.on("TrailMapView:removemedia", this.onTrailMapViewRemoveMedia, this);
      app.dispatcher.on("TrailMapView:movedmedia", this.onTrailMapViewMoveMedia, this);
      app.dispatcher.on("TrailMapView:starmedia", this.onTrailMapViewStarMedia, this);      

      app.dispatcher.on("TrailSlideshowView:mediaclick", this.onTrailSlideshowViewMediaClick, this);
      app.dispatcher.on("TrailSlideshowView:mediaupdate", this.onTrailSlideshowViewMediaUpdate, this);
      app.dispatcher.on("TrailSlideshowView:mediaremove", this.onTrailSlideshowViewMediaRemove, this);

      this.nState = STATE_UPLOAD;
      this.timezoneData = null;      
      this.bRendered = false;
    },
    getStarMediaID: function(){
      return this.model.starID;
    },
    render: function(){
      if (this.bRendered) {
        return;
      }
      this.bRendered = true;
                            
	  var self = this;                            
                            
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      this.overlayView = new OverlayView({ el: '#tb-overlay-view', model: this.model });
      this.trailUploadPhotoView = new TrailUploadPhotoView({ el: '#uploadPhoto_view', model: this.model });
      this.trailSlideshowView = new TrailSlideshowView({ el: '#slideshow_view', collection: this.options.mediaCollection });
      this.trailActivitiesView = new TrailActivitiesView({ el: '#trailactivities_view', model: this.model });

      this.trailUploadPhotoView.render();          

	  this.renderTrailDetail();
      
      $('.submit', $(this.el)).click(function(evt) {
        // fire event
        app.dispatcher.trigger('TrailEditView:submitclick', self);                        
      });

	  $('#form_trail_name').keyup(function() {
        // fire event
        app.dispatcher.trigger('TrailEditView:fieldkeypress', self);                        
	  });
	  	  
      return this;
    },
    renderTrailDetail: function(){   
      var self = this;
                   
      $('#form_trail_name').val(this.model.get('value').route.name);
      $('#form_trail_region').val(this.model.get('value').route.region);
      $('#form_trail_notes').val(this.model.get('value').route.about);
      
      $('.update_details', $(this.el)).click(function(evt) {      	      
        var btn = $(this);
        btn.text('Update Details');
        btn.button('loading');
        setTimeout(function () {
            btn.button('reset');
        }, 2000);
      	
        self.model.get('value').route.name = $('#form_trail_name').val();
        self.model.get('value').route.region = $('#form_trail_region').val();
        self.model.get('value').route.about = $('#form_trail_notes').val();
		self.model.get('value').route.route_category_id = $('#trail_types').find('[data-bind="label"]').attr('data-id');
		
	  	self.renderTrailCard();                      		
        // fire event
        app.dispatcher.trigger("TrailEditView:updatedetailsclick", self);                        
      });
            
      // get trail types
	  var elList = $('#trail_types ul', $(this.el));
      var strURL = TB_RESTAPI_BASEURL + '/v1/route_category/list';      
      $.ajax({
        type: "GET",
        dataType: "json",
        url: strURL,
        error: function(data) {
//          console.log('error:'+data.responseText);      
        },
        success: function(data) {      
          // populate list
	      $.each(data.value.route_types, function(key, routeType) {
	      	elList.append('<li role="presentation" data-id="'+routeType.id+'"><a role="menuitem" tabindex="-1" href="#">'+routeType.name+'</a></li>');
	      });
	      // set curr sel
      	  var nCategoryID = 1;
	      if (self.model.get('value').route.category != undefined) {
      	    nCategoryID = self.model.get('value').route.category.id;
	      }
	      self.model.get('value').route.route_category_id = nCategoryID;
	      
	      var elItem = $('#trail_types li[data-id='+nCategoryID+']');
      	  if (elItem.length) {
   			$('#trail_types').find('[data-bind="label"]').text(elItem.eq(0).text()).attr('data-id', nCategoryID);
      	  }
	  	  // list handler            
	  	  $('.dropdown-menu li', $(self.el)).click(function(evt) { 
        	var $target = $(evt.currentTarget);
   			$target.closest('.dropdown')
      		.find('[data-bind="label"]').text($target.text()).attr('data-id', $target.attr('data-id'))
        	.end()
      		.children('.dropdown-toggle').dropdown('toggle');
 
   			return false; 
	  	  });
	  	  
	  	  self.renderTrailCard();                      
        }
      });      
      
      this.trailActivitiesView.render();            
    },
    renderTrailCard: function(){
      $('.trailcard_panel .trail_card_title', $(this.el)).html(this.model.get('value').route.name);
      $('.trailcard_panel .trail_card_region', $(this.el)).html(this.model.get('value').route.region);
	  // trail_card_category      	
	  $('.trailcard_panel .trail_card_category', $(this.el)).html($('#trail_types li[data-id='+this.model.get('value').route.route_category_id+']').text());        
    },
    renderTrailCardPhoto: function(){
      var self = this;
      
      var model = this.options.mediaCollection.get(this.model.starID);

	  var elContext = $('.trailcard_panel', $(self.el));
	  $('.image_container', elContext).removeClass('tb-fade-in').css('opacity', 0);
	  
	  if (this.options.mediaCollection.length) {
  	    $('.trailcard_panel .photo .image_container', $(this.el)).html('<img src="http://app.resrc.it/o=80/http://s3-eu-west-1.amazonaws.com/'+model.get('versions')[0].path+'" class="resrc scale" border="0"/>');	  	
	  }
	  else {
  	    $('.trailcard_panel .photo .image_container', $(this.el)).html('<img src="http://app.resrc.it/o=80/http://s3-eu-west-1.amazonaws.com/trailburning-assets/images/default/example_trailcard.jpg" class="resrc scale" border="0"/>');
	  }
        
	  // scale images when loaded
      var elImages = $('.scale', elContext);	    
      var imgLoad = imagesLoaded(elImages);
      imgLoad.on('always', function(instance) {
        for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
          $(imgLoad.images[i].img).addClass('scale_image_ready');
        }
        // update pos
        $('img.scale_image_ready', elContext).imageScale();
        // fade in - delay adding class to ensure image is ready  
        $('.fade_on_load', elContext).addClass('tb-fade-in');
        $('.image_container', elContext).css('opacity', 1);
      });
    },
    renderSlideshow: function(){
      this.updateStarSlide();
      this.trailSlideshowView.render();
      // update gallery
      this.trailSlideshowView.starSlide(this.options.model.starID);
	  this.trailSlideshowView.selectSlide(this.options.model.starID);
	  // update trail card      
      this.renderTrailCardPhoto();     
	},
    selectSlideshowSlide: function(mediaID){
      this.trailSlideshowView.selectSlide(mediaID);
	},    	
    updateStarSlide: function(){
      var model = null;
      // do we have an updated star?
	  if (this.options.model.starID) {	  	
        model = this.options.mediaCollection.get(this.options.model.starID);
	  }
      if (!model) {
	  	// is it valid?
	  	if (this.options.model.get('value').route.media) {
          model = this.options.mediaCollection.get(this.options.model.get('value').route.media.id);	  		
	  	}
        if (!model) {
          model = this.options.mediaCollection.at(0);
        }
        if (model) {
          this.options.model.starID = model.id;	
        }
        else {
          this.options.model.starID = 0;
        }
      }
	},
    onTrailUploadPhotoViewUpload: function(trailUploadPhotoView){
      $('#tb-content-overlay').show();      
      $('#tb-overlay-view').show();
      this.overlayView.render();      
      this.trailUploadPhotoProgressView = new TrailUploadPhotoProgressView({ el: '#overlayContent_view', model: this.model, bMultiUpload: trailUploadPhotoView.multiUpload() });
      this.trailUploadPhotoProgressView.render();
    },
    onTrailUploadPhotoViewUploaded: function(trailUploadPhotoView){
      $('#tb-content-overlay').hide();      
      $('#tb-overlay-view').hide();      
      // render again to re-attach change event
      this.trailUploadPhotoView.render();
            
      // fire event
      app.dispatcher.trigger("TrailEditView:photouploaded", trailUploadPhotoView);
    },
    onTrailUploadPhotoViewUploadProgress: function(nProgress){
      this.trailUploadPhotoProgressView.render(nProgress);
    },
    onTrailUploadPhotoViewError: function(trailUploadPhotoView){
      $('#uploadPhotoerror_view').show();
      
      this.trailUploadPhotoErrorView = new TrailUploadPhotoErrorView({ el: '#overlayContent_view', model: this.model, bMultiUpload: trailUploadPhotoView.multiUpload() });
      this.trailUploadPhotoErrorView.render();
    },
    onTrailUploadPhotoErrorViewCloseClick: function(trailUploadPhotoErrorView){
      $('#tb-content-overlay').hide();      
      $('#tb-overlay-view').hide();      
      // render again to re-attach change event
      this.trailUploadPhotoView.render();
    },
    onTrailMapViewMediaClick: function(mediaID){
      this.trailSlideshowView.gotoSlide(mediaID);
	},    
    onTrailMapViewRemoveMedia: function(mediaID){
      // remove from collection
	  this.options.mediaCollection.remove(mediaID);
      this.trailSlideshowView.remove(mediaID);
      
      var strURL = TB_RESTAPI_BASEURL + '/v1/media/' + mediaID;      
      $.ajax({
        url: strURL,
        type: 'DELETE',            
        complete : function(res) {
//          console.log('complete');              
        },
        success: function(data) {
//          console.log('msg:'+data.message);
        },
      });
      
	  // fire event
      app.dispatcher.trigger("TrailEditView:removemedia", this);                                    
    },
    onTrailMapViewStarMedia: function(mediaID){
      this.options.model.starID = mediaID;
      // update gallery
      this.trailSlideshowView.starSlide(this.options.model.starID);
	  // update trail card      
      this.renderTrailCardPhoto();
	  // fire event
      app.dispatcher.trigger("TrailEditView:updatestarphoto", this);                              
	},    
    onTrailMapViewMoveMedia: function(mediaID){
      // update gallery
      this.trailSlideshowView.sort();
      
      var model = this.options.mediaCollection.get(mediaID);
      var postData = JSON.stringify(model.toJSON());
      var postArray = {json:postData};

      var strURL = TB_RESTAPI_BASEURL + '/v1/media/' + mediaID;      
      $.ajax({
        type: "PUT",
        dataType: "json",
        url: strURL,
        data: postArray,
        error: function(data) {
//          console.log('error:'+data.responseText);      
//          console.log(data);      
        },
        success: function(data) {      
//          console.log('success');
//          console.log(data);
        }
      });        
    },    
    onTrailSlideshowViewMediaClick: function(mediaID){
      // fire event
      app.dispatcher.trigger("TrailEditView:galleryphotoclick", mediaID);                              
    },
    onTrailSlideshowViewMediaUpdate: function(){
	  this.renderTrailCardPhoto();
    },
    onTrailSlideshowViewMediaRemove: function(){
      this.updateStarSlide();
      // update gallery
      this.trailSlideshowView.starSlide(this.options.model.starID);
	  this.trailSlideshowView.selectSlide(this.options.model.starID);
	  this.renderTrailCardPhoto();
    }
        
  });

  return TrailmakerTrailEditView;
});
