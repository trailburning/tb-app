var app = app || {};

define([
  'underscore', 
  'backbone',
  'gmaps'
], function(_, Backbone, Gmaps){
  app.dispatcher = _.clone(Backbone.Events);
  
  var initialize = function() {
    var self = this;
    
    $(window).resize(function() {
      handleResize(); 
    });    
    handleResize();        

    $('#search_field').focus(function(evt) {
      $('#search_field').val('not just yet...');
      event.preventDefault();
    });

    var imgLoad = imagesLoaded('.scale');
    imgLoad.on('always', function(instance) {
      for ( var i = 0, len = imgLoad.images.length; i < len; i++ ) {
        $(imgLoad.images[i].img).addClass('scale_image_ready');
      }
      // update pos
      $("img.scale_image_ready").imageScale();
      // fade in - delay adding class to ensure image is ready  
      $('.fade_on_load').addClass('tb-fade-in');
      $('.image_container').css('opacity', 1);
    });
        
    function handleResize() {
      $("img.scale_image_ready").imageScale();
    }
    
    $('#footerview').show();
    

    // add input text element for the location autlosuggest
    $('#fos_user_registration_form_location').parent().append('<input type="text" id="location_autosuggest" name="" />');
    
    // hide the location input field that will hold the raw long tat value of the location
    $('#fos_user_registration_form_location')
        .css('position', 'absolute')
        .css('top', '-9999px')
        .css('left', '-9999px');
    
    
    $('#fos_user_registration_form_location').attr('tabindex', 999)
    
    // remove html5 checking because the field gets hidden from the user
    $('#fos_user_registration_form_location').removeAttr('required');
    
    // Create the autocomplete object, restricting the search
    // to geographical location types.
    var autocomplete = new Gmaps.places.Autocomplete(
        /** @type {HTMLInputElement} */(document.getElementById('location_autosuggest')),
        { types: ['(regions)'] });
    
    // When the user selects an address from the dropdown, set the location
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var place = autocomplete.getPlace();
        $('#fos_user_registration_form_location').val(place.geometry.location.toString());
    });
    
    // sets the users current location as preferred area within which to return Place result
    $('#location_autosuggest').focus(function () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var geolocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                autocomplete.setBounds(new google.maps.LatLngBounds(geolocation, geolocation));
            });
        }
    });
       
  };
    
  return { 
    initialize: initialize
  };   
});  
