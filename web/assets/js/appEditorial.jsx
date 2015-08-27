/**
 * @jsx React.DOM
 */
/*jshint quotmark:false */
/*jshint white:false */
/*jshint trailing:false */
/*jshint newcap:false */
/*global React, Backbone */
var app = app || {};

var STATE_BIG_SPONSOR = 1;
var STATE_SMALL_SPONSOR = 2;

(function () {
  'use strict';

  app.dispatcher = _.clone(Backbone.Events);

  var nSponsorState = STATE_BIG_SPONSOR;
  var nPrevScrollY = 0;

  var collectionPosts = new Backbone.Collection();
  var modelPost = null;

  var SlideList, slideList, DialogDetail, dialogDetail;
  var feed, nCurrSlideFocus = -1;    
  var elPhotoFeedContainer = $('#slideList-mount-point').get(0);
  var elDialogDetailContainer = $('#postDetail-mount-point').get(0);

  getFeed();

  // Register event handler for when media point is clicked
  Piste.mediaPointClicked = function ( id ) {
//    console.log( 'Clicked media point', id );

    // When clicked, pan to media point
    Piste.panToMediaPoint( id );

    // Also select clicked point (note multiple selection also supported)
    Piste.selectMediaPoints( [id] );

    renderSlideList(id);
  };

  // keyboard control
  $(document).keydown(function(e){
    switch (e.keyCode) {
      case 13: // detail dialog
        if (nCurrSlideFocus != -1) {
          $("#postDetail").modal();  
        }        
        break;
      case 37: // previous
        slideList.prevSlide();
        break;
      case 39: // next
        slideList.nextSlide();
        break;
    }
  });

  $("#postDetail").click(function(evt){
    if ($(evt.target).attr("id") == "postDetail") {
      $("#postDetail").modal("hide");  
    }
  });

  $(window).scroll(function () {
      handleScroll(); 
    });    
  
  $(window).bind('touchmove',function(e){
    handleScroll();
  });    
    
  $(window).resize(function() {
    handleResize(); 
  });                
    
  var searchView = new SearchView({ el: '#searchview' });
  if (typeof TB_USER_ID != 'undefined') {
    var activityFeedView = new ActivityFeedView({ el: '#activity_feed_view' });
    activityFeedView.render();
    activityFeedView.getActivity();      
  }
  
  $('#column_wrapper_intro').columnize({
  columns : 2,
  accuracy : 1,
  lastNeverTallest: true,
  buildOnce : true
  });

  $('.card_column_wrapper').columnize({
  columns : 2,
  accuracy : 1,
  lastNeverTallest: true,
  buildOnce : true
  });    

  $('.column_wrapper').css('visibility', 'visible');
  $('#footerview').show();
  
  handleResize();

  function handleResize() {
    $("img.scale_image_ready").imageScale();
  }

  function handleScroll() {
    var nTopY = 45;
    var nTransitionOffY = 35;
    var nTransitionOnY = 12;
    var nScrollY = ($(window).scrollTop() < 0) ? 0 : $(window).scrollTop();   
    var nFactorY = 2;
    var bScrollUp = false;

    // which direction are we scrolling?    
    if (nScrollY > nPrevScrollY) {
      bScrollUp = true;     
    }
    
    if (Modernizr.touch) {
      switch (nSponsorState) {
        case STATE_BIG_SPONSOR:
          if ((nScrollY > $('#big_sponsor_bar').height()) && bScrollUp) {
            nSponsorState = STATE_SMALL_SPONSOR;
            $('#small_sponsor_bar').show();
            $('#big_sponsor_bar').hide();
          }
          break;
          
        case STATE_SMALL_SPONSOR:
          if ((nScrollY < nTransitionOnY) && !bScrollUp) {
            nSponsorState = STATE_BIG_SPONSOR;
            $('#small_sponsor_bar').hide();
            $('#big_sponsor_bar').show();
          }
          break;  
      }      
    }
    else {
      // move big bar
      $('#big_sponsor_bar').css('top', nTopY - (nScrollY * nFactorY));  
      
      switch (nSponsorState) {
        case STATE_BIG_SPONSOR:
          if ((nScrollY > nTransitionOffY) && bScrollUp) {
            nSponsorState = STATE_SMALL_SPONSOR;
            $('#small_sponsor_bar').css('top', nTopY);
            $('#small_sponsor_bar').css('visibility', 'visible');
          }
          break;
          
        case STATE_SMALL_SPONSOR:
          if ((nScrollY < nTransitionOnY) && !bScrollUp) {
            nSponsorState = STATE_BIG_SPONSOR;
            $('#small_sponsor_bar').css('visibility', 'hidden');
            $('#small_sponsor_bar').css('top', 0);
          }
          break;
       }
    }   
    nPrevScrollY = nScrollY;
  } 

  function getFeed() {
    var url = "http://www.eggontop.com/live/trailburning/tb-campaignviewer/server/feed_ultraks3d.php";
    
    var strInstagramURL = "http://www.instagram.com/";

    $.getJSON(url, function(result){
      if(!result || !result.data || !result.data.length){
        return;
      }  

      feed = result.data;
       
      var fLat, fLng;
      $.each(result.data, function(index, item) {
        if (item.location) {
          fLat = item.location.latitude;
          fLng = item.location.longitude;

          var date = new Date(parseInt(item.created_time) * 1000);
          var strCaption = "";
          if (item.caption) {
            strCaption = item.caption.text;
          }

          modelPost = new Backbone.Model({
            id: item.id, 
            image_low_res: item.images.low_resolution.url, 
            image_standard_res: item.images.standard_resolution.url, 
            caption: strCaption,
            lat: fLat, 
            lng: fLng,
            link_url: item.link,
            username: item.user.username,
            user_url: strInstagramURL + item.user.username,
            user_avatar: item.user.profile_picture,
            created_time: date.getTime()
          });
          collectionPosts.add(modelPost);
        }
      });

      var nInitialSlide = 0;

      SlideList = app.SlideList;
      DialogDetail = app.DialogDetail;

      renderSlideList(nInitialSlide);

      $("#slideList-mount-point").swipe( { allowPageScroll:"vertical",
        //Generic swipe handler for all directions
        swipe:function(event, direction, distance, duration, fingerCount, fingerData) {
          if (direction == "left") {
            slideList.prevSlide();
          }
          if (direction == "right") {
            slideList.nextSlide();              
          }
        }
      });      
    });
  }

  function renderSlideList(nSelected) {
    slideList = React.render(<SlideList collection={ collectionPosts.models } selected={ nSelected } onSlideFocus={ onSlideFocus } onSlideClick={ onSlideClick } />, elPhotoFeedContainer);
  }

  function renderDialogDetail(nSelected) {
    var modelPost = collectionPosts.at(nSelected);    
    dialogDetail = React.render(<DialogDetail link_url={ modelPost.get("link_url") } user_url={ modelPost.get("user_url") } username={ modelPost.get("username") } user_avatar={ modelPost.get("user_avatar") } created_time={ modelPost.get("created_time") } caption={ modelPost.get("caption") } image_standard_res={ "" } onPrevClick={ onPrevClick } onNextClick={ onNextClick } />, elDialogDetailContainer);
    dialogDetail = React.render(<DialogDetail link_url={ modelPost.get("link_url") } user_url={ modelPost.get("user_url") } username={ modelPost.get("username") } user_avatar={ modelPost.get("user_avatar") } created_time={ modelPost.get("created_time") } caption={ modelPost.get("caption") } image_standard_res={ modelPost.get("image_standard_res") } onPrevClick={ onPrevClick } onNextClick={ onNextClick } />, elDialogDetailContainer);
  }  

  function onSlideFocus(nSelected) {
    if (nSelected != nCurrSlideFocus) {
      // When clicked, pan to media point
      Piste.panToMediaPoint( nSelected );
      // Also select clicked point (note multiple selection also supported)
      Piste.selectMediaPoints( [nSelected] );
    }

    nCurrSlideFocus = nSelected;

    renderDialogDetail(nSelected);
  }

  function onSlideClick(nSelected) {
    renderDialogDetail(nSelected);
    $("#postDetail").modal();
  }

  function onPrevClick() {
    slideList.prevSlide();
  }

  function onNextClick() {
    slideList.nextSlide();
  }

})();
