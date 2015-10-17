define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var EventSlideshowView = Backbone.View.extend({
    initialize: function(options){
      this.options = options;

      this.template = _.template($('#eventSlideshowViewTemplate').text());

      this.arrMedia = Array();
      this.nContainerWidth = 0;
      this.nCurrentSlide = 0;
    },

    hide: function(){
      $.each(this.arrMedia, function(index, objMedia) {
        objMedia.pause();
        objMedia.dispose();
      });
      while (this.arrMedia.length) { 
        this.arrMedia.pop(); 
      }
      $(this.el).hide();
    },
    show: function(){
      var self = this, objMedia = null;

      $('.media-player', $(this.el)).each(function(){
        objMedia = videojs($(this).attr('id'));
        self.arrMedia.push(objMedia);

        objMedia.src($(this).attr('url'));
        objMedia.load();
      });
      $(this.el).show();
    },
    resize: function(nHeightAspectPercent){
      var self = this;

      $('.slideshow-container', $(this.el)).removeClass('transition');

      $('.slideshow-view', $(this.el)).each(function(){
        self.nContainerWidth = $(this).width();

        var elSlides = $(".slide-view", $(this));
        elSlides.each(function(){
          $(this).width(self.nContainerWidth);
        });
  
        var elContainers = $(".scale-container", $(this));
        elContainers.each(function(){
          var nHeight = ($(this).width() * nHeightAspectPercent) / 100;
          $(this).height(nHeight);
        });
        $('.slideshow-container', $(this)).width(elContainers.length * self.nContainerWidth);
      });
      $('.slideshow-container', $(this.el)).css('left', -(this.nCurrentSlide * this.nContainerWidth));
    },
    render: function(assetModel, assetCollection, strEventCategory){
      // seed slide
      this.nCurrentSlide = assetCollection.indexOf(assetModel);

      this.model = assetModel;
      this.collection = assetCollection;

      var self = this;

      this.model.set('category', strEventCategory);
      this.model.set('journeyAssets', assetCollection.toJSON());

      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      $(this.el).swipe( { swipeStatus:swipe1, allowPageScroll: "vertical"} );
      function swipe1(event, phase, direction, distance) {
        if (direction == 'left') {
          self.onNextSlide();
        }
        else if (direction == 'right') {
          self.onPrevSlide();
        }
      }

      $('.prev-btn', $(this.el)).click(function(evt){
        self.onPrevSlide();
      });
      $('.next-btn', $(this.el)).click(function(evt){
        self.onNextSlide();
      });

      return this;
    },
    onPrevSlide: function(){
      var nSlide = this.nCurrentSlide - 1;
      if (nSlide < 0) {
        return;
      }
      this.nCurrentSlide = nSlide;

      $.each(this.arrMedia, function(index, objMedia) {
        objMedia.pause();
      });

      $('.slideshow-container', $(this.el)).addClass('transition');
      $('.slideshow-container', $(this.el)).css('left', -(this.nCurrentSlide * this.nContainerWidth));
    },
    onNextSlide: function(){
      var nSlide = this.nCurrentSlide + 1;
      if (nSlide >= this.collection.length) {
        return;
      }
      this.nCurrentSlide = nSlide;

      $.each(this.arrMedia, function(index, objMedia) {
        objMedia.pause();
      });

      $('.slideshow-container', $(this.el)).addClass('transition');
      $('.slideshow-container', $(this.el)).css('left', -(this.nCurrentSlide * this.nContainerWidth));
    }

  });

  return EventSlideshowView;
});
