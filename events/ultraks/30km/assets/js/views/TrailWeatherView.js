define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var WEATHER_BASEURL = 'http://api.openweathermap.org/data/2.5/weather';
  var WEATHER_ICONS_BASEURL = 'https://s3-eu-west-1.amazonaws.com/trailburning-assets/images/icons/weather/';
  var UNITS_METRIC = 0;
  var UNITS_IMPERIAL = 1;

  var TrailWeatherView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#trailWeatherViewTemplate').text());        
            
      this.bRendered = false;
      this.nUnits = UNITS_METRIC;  
//      this.nUnits = UNITS_IMPERIAL;  
    },            
    render: function(){
      var self = this;
      
      function updateWeather(jsonData) {
        var elField, strText;
        
        // description
        elField = $('.desc', $(self.el));
        elField.html(jsonData['weather'][0].description);        
        // temp
        elField = $('.temp', $(self.el));
        switch (self.nUnits) {
          case UNITS_METRIC:           
            strText = '<h1>'+Math.round(jsonData['main'].temp)+'°</h1><h2>Celsius Temperature</h2>'
            break;
          case UNITS_IMPERIAL:
            strText = '<h1>'+Math.round(jsonData['main'].temp * 3.8)+'°</h1><h2>Fahrenheit Temperature</h2>'
            break;          
        }        
        elField.html(strText);
        // wind
        elField = $('.wind', $(self.el));
        // metres per second to km per hour
        strText = Math.round(jsonData['wind'].speed * 3.6) + ' km/h';
        switch (self.nUnits) {
          case UNITS_IMPERIAL:
            // feet per second to mph
            strText = Math.round(jsonData['wind'].speed / 0.44704) + ' mph';
            break;          
        }
        elField.html('<h1>'+strText+'</h1><h2>Wind</h2>');
        // icon
        switch (jsonData['weather'][0].icon) {
          case '01d':
          case '01n':
            strText = 'sun.png';
            break;

          case '02d':
          case '02n':
            strText = 'sun_cloud.png';
            break;

          case '03d':
          case '03n':
          case '04d':
          case '04n':
            strText = 'cloud.png';
            break;

          case '09d':
          case '09n':
            strText = 'sun_rain.png';
            break;

          case '10d':
          case '10n':
            strText = 'rain.png';
            break;

          case '11d':
          case '11n':
            strText = 'storm.png';
            break;

          case '13d':
          case '13n':
            strText = 'snow.png';
            break;

          case '50d':
          case '50n':
            strText = 'fog.png';
            break;
            
          default:
            strText = 'sun_cloud.png';
            break;
        }
        elField = $('.icon', $(self.el));        
        elField.html('<img src="'+WEATHER_ICONS_BASEURL+strText+'"/>');
      }
      function updateWeatherERR() {
        var strText = 'is unknown';
        
        // description
        elField = $('.desc', $(self.el));
        elField.html(strText);     
      }
      
      if (!this.model) {
        return;
      }

      if (!this.bRendered) {
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
        
        var jsonRoute = this.model.get('value').route;
        
        // get weather
        var strUnits = '';
        switch (this.nUnits) {
          case UNITS_METRIC:
            strUnits = 'metric';
            break;
            
          case UNITS_IMPERIAL:
            strUnits = 'imperial';
            break;
        }
        var strURL = WEATHER_BASEURL + '?lat='+jsonRoute.route_points[0].coords[1]+'&lon='+jsonRoute.route_points[0].coords[0] + '&units=metric';
//        console.log(strURL);
        $.ajax({
          dataType: "jsonp",
          url: strURL,
          success: function(data) {
            updateWeather(data);
          },
          error: function(data) {
            updateWeatherERR();
          }                     
        });        
      }                             
      return this;
    }    
  });

  return TrailWeatherView;
});
