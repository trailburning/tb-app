define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailAltitudeView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#traiAltitudeViewTemplate').text());        
            
      this.HeightToWidthFactor = 4;
      
      this.fXFactor = 0;
      this.fYFactor = 0;

      this.jsonTrail = null;
    },            
    render: function(fScale){
      console.log('TrailAltitudeView:render');
        
      if (!this.model) {
        return;
      }

      if (!this.model.get('id')) {
        return;
      }

      var self = this;
                
      var attribs = this.model.toJSON();
      $(this.el).html(this.template(attribs));

      this.elCanvas = $('#graph', this.el);
      this.elLineGraph = $('.linegraph', this.el);
      this.elCanvas.width = this.elLineGraph.width();
      this.elCanvas.height = this.elLineGraph.height(); 
      
      this.canvas = this.elCanvas[0];
      this.context = this.canvas.getContext('2d');      
      
//      this.nCanvasWidth = this.canvas.width;
//      this.nCanvasHeight = this.canvas.height; // draw from 1 not 0
      this.nCanvasWidth = this.elLineGraph.width();
      this.nCanvasHeight = this.elLineGraph.height(); // draw from 1 not 0

      this.context.clearRect(0,0,200,200);

      this.nDrawWidth = this.nCanvasWidth;
      this.nDrawHeight = this.nCanvasHeight;
            
      var data = this.model.get('value');            
      this.renderTrail(data.route.route_points, Number(data.route.length));

      this.context.lineWidth = 4;
      this.context.strokeStyle = 'rgba(68,182,252,1)';
      this.context.stroke();

      return this;
    },
    update: function() {
      if (this.jsonTrail) {
        this.render();      
      }
    },
    renderTrail: function(jsonPoints, fTrailLengthMetres) {
      this.jsonTrail = jsonPoints;
            
       // find highest/lowest points
      if (jsonPoints.length) {
        fLowAlt = jsonPoints[0].tags.altitude;
        fHighAlt = jsonPoints[0].tags.altitude;
      }
      $.each(jsonPoints, function(key, point) {
        if (Number(point.tags.altitude) < fLowAlt) {
          fLowAlt = Number(point.tags.altitude); 
        }
        if (Number(point.tags.altitude) > fHighAlt) {
          fHighAlt = Number(point.tags.altitude); 
        }    
      });     
      
      var fRange = fHighAlt - fLowAlt;
      
      var fProfileWidthMetres = fTrailLengthMetres;
      var fProfileHeightMetres = fRange * this.HeightToWidthFactor;
        
      var nRatio = Math.round(fProfileWidthMetres / fProfileHeightMetres); 

      // adjust draw width
      this.nDrawWidth = this.nDrawHeight * nRatio;
      // scale to fit canvas        
      if (this.nDrawWidth > this.nCanvasWidth || this.nDrawHeight > this.nCanvasHeight) {
        this.nDrawHeight = Math.round(this.nDrawHeight * this.nCanvasWidth / this.nDrawWidth);
        this.nDrawWidth = this.nCanvasWidth
      }  

      this.fXFactor = jsonPoints.length / this.nDrawWidth;
      this.fYFactor = fHighAlt / this.nDrawHeight;

      var self = this;
      var nXOffset = (this.nCanvasWidth - this.nDrawWidth) / 2;
      var nX = 0;
      var nY = 0;
      var nYPercent = 0;
      var rem = 0;
      
      self.context.beginPath();
      // draw first point
      if (jsonPoints.length) {        
        nYPercent = ((jsonPoints[0].tags.altitude - fLowAlt) / fRange) * 100;
        nY = 1 + Math.round((self.nDrawHeight-2) - ((nYPercent * (self.nDrawHeight-2)) / 100));
        self.context.moveTo(0, nY);
      }
      
      var nStartX = 0;  
      fLowAlt = Math.round(fLowAlt);
      $.each(jsonPoints, function(key, point) {
        rem = key % Math.round(self.fXFactor * 4);
        if (rem == 0) {
          nX = nXOffset + Math.round(key / self.fXFactor);
          nYPercent = ((point.tags.altitude - fLowAlt) / fRange) * 100;
          nY = 1 + Math.round((self.nDrawHeight-2) - ((nYPercent * (self.nDrawHeight-2)) / 100));
          self.context.lineTo(nX, nY);            
          if (!nStartX) {
            nStartX = nX;
          }
        }
      });      
      var nEndX = nX;
      
      // draw last point
      if (jsonPoints.length) {     
        self.context.lineTo(self.nCanvasWidth, nY);
      }        
    }
  });

  return TrailAltitudeView;
});
