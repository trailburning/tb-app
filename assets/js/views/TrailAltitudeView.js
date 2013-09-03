define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailAltitudeView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#traiAltitudeViewTemplate').text());        
            
      this.HeightToWidthFactor = 4;      
      this.fXFactor = 0;
      this.jsonTrail = null;
      this.fLowAlt = 0;
      this.fHighAlt = 0;
      
      this.objTrailMarginRect = new Object();
      this.objTrailMarginRect.left = 50;
      this.objTrailMarginRect.right = 50;
      this.objTrailMarginRect.top = 20;
      this.objTrailMarginRect.bottom = 20;
      
      this.objBackgroundMarginRect = new Object();
      this.objBackgroundMarginRect.left = 50;
      this.objBackgroundMarginRect.right = 50;
      this.objBackgroundMarginRect.top = 20;
      this.objBackgroundMarginRect.bottom = 60;
      
      CanvasRenderingContext2D.prototype.dashedVerticalLine = function(nX, nY1, nY2, nDashStrokeHeight) {
        if (nDashStrokeHeight == undefined) nDashStrokeHeight = 2;

        var nDashHeight = 7;
        var nDashes = Math.floor(nY2 - nY1) / nDashHeight;
        
        var nDash = 0;
        var nCurrY = nY1;
        while (nDash++ < nDashes) {
          this.moveTo(nX, nCurrY);            
          this.lineTo(nX, nCurrY + nDashStrokeHeight);
          
          nCurrY += nDashHeight;
        }
      };      
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
                  
      this.canvas = this.elCanvas[0];

      this.nCanvasWidth = $(this.el).width();
      this.nCanvasHeight = $(this.el).height();

      this.elCanvas.attr('width', this.nCanvasWidth);
      this.elCanvas.attr('height', this.nCanvasHeight);
      this.elCanvas.width = this.nCanvasWidth;
      this.elCanvas.height = this.nCanvasHeight; 
      
      this.context = this.canvas.getContext('2d');      

      this.nDrawWidth = this.nCanvasWidth;
      this.nDrawHeight = this.nCanvasHeight;
            
      var data = this.model.get('value');
      
      this.updateValues(data.route.route_points, Number(data.route.length));      
      this.renderBackground(Number(data.route.length));                  
      this.renderTrail(data.route.route_points, Number(data.route.length));

      return this;
    },
    update: function() {
      if (this.jsonTrail) {
        this.render();      
      }
    },    
    updateValues: function(jsonPoints, fTrailLengthMetres) {
      var self = this;
      
      this.nCanvasDrawWidth = $(this.el).width() - (this.objTrailMarginRect.left + this.objTrailMarginRect.right);
      this.nCanvasDrawHeight = $(this.el).height() - (this.objTrailMarginRect.top + this.objTrailMarginRect.bottom);
      
      // find highest/lowest points
      if (jsonPoints.length) {
        this.fLowAlt = Number(jsonPoints[0].tags.altitude);
        this.fHighAlt = Number(jsonPoints[0].tags.altitude);
      }
      $.each(jsonPoints, function(key, point) {
        if (Number(point.tags.altitude) < self.fLowAlt) {
          self.fLowAlt = Number(point.tags.altitude); 
        }
        if (Number(point.tags.altitude) > self.fHighAlt) {
          self.fHighAlt = Number(point.tags.altitude); 
        }    
      });     
      
      this.fAltRange = this.fHighAlt - this.fLowAlt;
      
      var fProfileWidthMetres = fTrailLengthMetres;
      var fProfileHeightMetres = this.fAltRange * this.HeightToWidthFactor;
        
      var nRatio = Math.round(fProfileWidthMetres / fProfileHeightMetres); 

      // adjust draw width
      this.nDrawWidth = this.nDrawHeight * nRatio;
      // scale to fit canvas        
      if (this.nDrawWidth > this.nCanvasDrawWidth || this.nDrawHeight > this.nCanvasDrawHeight) {
        this.nDrawHeight = Math.round(this.nDrawHeight * this.nCanvasDrawWidth / this.nDrawWidth);
        this.nDrawWidth = this.nCanvasDrawWidth;
      }  
    },    
    renderBackground: function(fTrailLengthMetres) {
      this.context.beginPath();
      
      var nMarkers = fTrailLengthMetres / 1000;
      var nMarkerWidth = this.nDrawWidth / nMarkers;

      var nX = this.objBackgroundMarginRect.left;
      for (var nMarker=0; nMarker <= nMarkers; nMarker++) {
        this.context.dashedVerticalLine(Math.round(nX), this.objBackgroundMarginRect.top, this.nCanvasHeight - this.objBackgroundMarginRect.bottom, 2);
        nX += nMarkerWidth;
      }
      
      this.context.lineWidth = 1;
      this.context.strokeStyle = 'rgba(68,182,252,1)';
      this.context.stroke();
    },    
    renderTrail: function(jsonPoints, fTrailLengthMetres) {
      this.jsonTrail = jsonPoints;
                              
      this.fXFactor = jsonPoints.length / this.nDrawWidth;

      var self = this;
      var nXOffset = (this.nCanvasDrawWidth - this.nDrawWidth) / 2;
      var nYOffset = (this.nCanvasDrawHeight - this.nDrawHeight) / 2;
            
      var nX = 0;
      var nY = 0;
      var nYPercent = 0;
      var rem = 0;
      
      this.context.beginPath();
      // draw first point
      if (jsonPoints.length) {        
        nYPercent = ((jsonPoints[0].tags.altitude - this.fLowAlt) / this.fAltRange) * 100;
        nY = this.objTrailMarginRect.top + nYOffset + Math.round((self.nDrawHeight-2) - ((nYPercent * (self.nDrawHeight-2)) / 100));
        self.context.moveTo(this.objTrailMarginRect.left, nY);
      }
      
      var nStartX = 0;  
      $.each(jsonPoints, function(key, point) {
        rem = key % Math.round(self.fXFactor * 4);
        if (rem == 0) {
          nX = nXOffset + self.objTrailMarginRect.left + Math.round(key / self.fXFactor);
          nYPercent = ((point.tags.altitude - Math.round(self.fLowAlt)) / self.fAltRange) * 100;
          nY = nYOffset + self.objTrailMarginRect.top + Math.round((self.nDrawHeight-2) - ((nYPercent * (self.nDrawHeight-2)) / 100));
          self.context.lineTo(nX, nY);            
          if (!nStartX) {
            nStartX = nX;
          }
        }
      });      
      var nEndX = nX;
      
      // draw last point
      if (jsonPoints.length) {     
        this.context.lineTo(self.objTrailMarginRect.left + self.nDrawWidth, nY);
      }        
      this.context.lineWidth = 4;
      this.context.strokeStyle = 'rgba(68,182,252,1)';
      this.context.stroke();      
    },
    addMediaMarker: function(nLat, nLng) {
      var jsonPoints = this.model.get('value').route.route_points;
      
      var self = this;
      var bFound = false;
      
      var nXOffset = (this.nCanvasDrawWidth - this.nDrawWidth) / 2;
      var nYOffset = (this.nCanvasDrawHeight - this.nDrawHeight) / 2;
      
      $.each(jsonPoints, function(key, point) {
        if (nLat == point.coords[1] && nLng == point.coords[0]) {
          var nX = nXOffset + self.objTrailMarginRect.left + Math.round(key / self.fXFactor);
          var nYPercent = ((point.tags.altitude - Math.round(self.fLowAlt)) / self.fAltRange) * 100;
          var nY = nYOffset + self.objTrailMarginRect.top + Math.round((self.nDrawHeight-2) - ((nYPercent * (self.nDrawHeight-2)) / 100));
          self.addMediaMarkerXY(nX, nY);
          bFound = true;        
        }
      });
      if (!bFound) {
        this.addMediaMarkerXY(0, 0);
      }
    },
    addMediaMarkerXY: function(nX, nY) {
      nX -= 10;
      nY -= 10;
      
      var elMarker = $('<div class="marker"></div>');
      var elProfile = $('.profile', this.el);
      elProfile.append(elMarker);

      elMarker.css('left', nX);
      elMarker.css('top', nY);      
    }
    
  });

  return TrailAltitudeView;
});
