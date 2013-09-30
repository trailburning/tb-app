define([
  'underscore', 
  'backbone'
], function(_, Backbone){

  var TrailAltitudeView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#traiAltitudeViewTemplate').text());        
            
      this.bRendered = false;      

      this.HeightToWidthFactor = 4;      
      this.fXFactor = 0;
      this.jsonTrail = null;
      this.fLowAlt = 0;
      this.fHighAlt = 0;
      this.arrMediaPoints = new Array();
      this.currElMarker = null;
      
      this.objTrailMarginRect = new Object();
      this.objTrailMarginRect.left = 50;
      this.objTrailMarginRect.right = 50;
      this.objTrailMarginRect.top = 20;
      this.objTrailMarginRect.bottom = 60;
      
      this.objBackgroundMargin = new Object();
      this.objBackgroundMargin.top = 20;
      this.objBackgroundMargin.bottom = 60;
      
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

      var self = this;      
      $(window).resize(function() {
        self.render();        
      });    
    },            
    gotoMedia: function(nMedia){
      // restpre previous
      if (this.currElMarker) {
        this.currElMarker.removeClass('marker_active');        
      }
      var elMarker = this.arrMediaPoints[nMedia];
      elMarker.addClass('marker_active');
      
      this.currElMarker = elMarker;
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
                
      if (!this.bRendered) {
        var attribs = this.model.toJSON();
        $(this.el).html(this.template(attribs));
  
        this.elCanvas = $('canvas', this.el);
        this.canvas = this.elCanvas[0];
        this.context = this.canvas.getContext('2d');      
      }
                
      // has the view changed sized?
      if (this.nCanvasWidth == $(this.el).width() &&
          this.nCanvasHeight == $(this.el).height()) {
        return;
      }                
                
      this.nCanvasWidth = $(this.el).width();
      this.nCanvasHeight = $(this.el).height();
      this.elCanvas.attr('width', this.nCanvasWidth);
      this.elCanvas.attr('height', this.nCanvasHeight);
      this.elCanvas.width = this.nCanvasWidth;
      this.elCanvas.height = this.nCanvasHeight; 
            
      var data = this.model.get('value');
      var jsonPoints = data.route.route_points;
      var fTrailLengthMetres = Number(data.route.length);

      this.nCanvasDrawWidth = $(this.el).width() - (this.objTrailMarginRect.left + this.objTrailMarginRect.right);
      this.nCanvasDrawHeight = $(this.el).height() - (this.objTrailMarginRect.top + this.objTrailMarginRect.bottom);

      this.nDrawWidth = this.nCanvasDrawWidth;
      this.nDrawHeight = this.nCanvasDrawHeight;
      
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

      this.renderBackground(fTrailLengthMetres);                  
      this.renderTrail(jsonPoints, fTrailLengthMetres);
      this.renderMarkers();

      this.bRendered = true;

      return this;
    },
    renderBackground: function(fTrailLengthMetres) {
      this.context.beginPath();

      this.context.fillStyle = 'rgba(68,182,252,1)';
      this.context.font = '16px Arial';
      this.context.textAlign = 'center';
      
      var nMarkerDistance = 1;
      if (fTrailLengthMetres > 20000) {
        nMarkerDistance = 2;
      }
      var nMarkers = fTrailLengthMetres / 1000;
      var nMarkerWidth = this.nDrawWidth / nMarkers;

      var nXOffset = (this.nCanvasDrawWidth - this.nDrawWidth) / 2;

      var nX = nXOffset + this.objTrailMarginRect.left;
      var strMarker = 'km';
      for (var nMarker=0; nMarker <= nMarkers; nMarker++) {
        if (!(nMarker % nMarkerDistance)) {
          this.context.dashedVerticalLine(Math.round(nX), this.objBackgroundMargin.top, this.nCanvasHeight - this.objBackgroundMargin.bottom, 2);
          this.context.fillText(strMarker, Math.round(nX), this.nCanvasHeight - this.objBackgroundMargin.bottom + 26);
          strMarker = (nMarker + nMarkerDistance);
        }
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
        nX = nXOffset + this.objTrailMarginRect.left;
        nYPercent = ((jsonPoints[0].tags.altitude - this.fLowAlt) / this.fAltRange) * 100;
        nY = nYOffset + this.objTrailMarginRect.top + Math.round((self.nDrawHeight-2) - ((nYPercent * (self.nDrawHeight-2)) / 100));
        self.context.moveTo(nX, nY);
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
        this.context.lineTo(nXOffset + self.objTrailMarginRect.left + self.nDrawWidth, nY);
      }        
      this.context.lineWidth = 4;
      this.context.strokeStyle = 'rgba(68,182,252,1)';
      this.context.stroke();      
    },
    renderMarkers: function() {
      var nXOffset = (this.nCanvasDrawWidth - this.nDrawWidth) / 2;
      var nYOffset = (this.nCanvasDrawHeight - this.nDrawHeight) / 2;
      
      var elMarker, nX, nY, nYPercent;
      
      for (var nMarker=0; nMarker < this.arrMediaPoints.length; nMarker++) {
        elMarker = this.arrMediaPoints[nMarker];
            
        nX = nXOffset + this.objTrailMarginRect.left + Math.round(elMarker.pos / this.fXFactor);
        nYPercent = ((elMarker.alt - Math.round(this.fLowAlt)) / this.fAltRange) * 100;
        nY = nYOffset + this.objTrailMarginRect.top + Math.round((this.nDrawHeight-2) - ((nYPercent * (this.nDrawHeight-2)) / 100));
              
        nX -= 10;
        nY -= 10;
      
        elMarker.css('left', nX);
        elMarker.css('top', nY);        
      }
    },
    addMediaMarker: function(nLat, nLng) {
      var jsonPoints = this.model.get('value').route.route_points;
      
      var elProfile = $('.profile', this.el);
      var self = this;
      $.each(jsonPoints, function(key, point) {
        if (nLat == point.coords[1] && nLng == point.coords[0]) {
            var elMarker = $('<div class="marker"></div>');
            elMarker.pos = key;
            elMarker.alt = point.tags.altitude;
            elProfile.append(elMarker);
            self.arrMediaPoints.push(elMarker);
        }
      });     
    }
    
  });

  return TrailAltitudeView;
});
