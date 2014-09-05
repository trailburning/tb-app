define([
  'underscore', 
  'backbone',
  'views/TrailMediaMarkerView'
], function(_, Backbone, TrailMediaMarkerView){

  var TrailAltitudeView = Backbone.View.extend({
    initialize: function(){
      this.template = _.template($('#traiAltitudeViewTemplate').text());        
                        
      this.bRendered = false;      

      this.fXFactor = 0;
      this.jsonTrail = null;
      this.fLowAlt = 0;
      this.fHighAlt = 0;
      this.arrMediaPoints = new Array();
      this.currElMarker = null;
      
      this.objTrailMarginRect = new Object();
      this.strLineColour = '#FFF';
      this.objTrailMarginRect.left = 75;// allow space to alt title
      this.objTrailMarginRect.right = 50;
      this.objTrailMarginRect.top = 40;
      this.objTrailMarginRect.bottom = 60;
      
      this.objBackgroundMargin = new Object();
      this.objBackgroundMargin.top = 20;
      this.objBackgroundMargin.bottom = 60;
      
      this.nMinHighAlt = 1000; // prevent low level trails from looking like mountains!
      
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
    gotoMedia: function(nMedia){
      // restore previous
      if (this.currMediaMarker) {
        this.currMediaMarker.setActive(false);
      }
      
      if (this.arrMediaPoints.length) {      
        this.currMediaMarker = this.arrMediaPoints[nMedia];
        this.currMediaMarker.setActive(true);
      }
    },    
    addMedia: function(mediaModel){
      var jsonPoints = this.model.get('value').route.route_points;
      var self = this;
      $.each(jsonPoints, function(key, point) {
        if (mediaModel.get('coords').lat == point.coords[1] && mediaModel.get('coords').long == point.coords[0]) {
          var trailMediaMarkerView = new TrailMediaMarkerView({ pos: key, model: mediaModel });
          self.arrMediaPoints.push(trailMediaMarkerView);
          return false;          
        }
      });           
    },        
    render: function(fScale){
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
      // is trail high enough to look like it's in the mountains?
      if (this.fHighAlt < this.nMinHighAlt) {
        if (this.fHighAlt) {
          this.fHighAlt = this.fHighAlt * 2;  
          this.fLowAlt = this.fLowAlt - 100;
        }
        else {
          this.fHighAlt = 100;  
          this.fLowAlt = 0;
        }
      }      
      
      this.fAltRange = this.fHighAlt - this.fLowAlt;
      
      this.nDrawHeight = Math.round(this.nDrawHeight * this.nCanvasDrawWidth / this.nDrawWidth);      
      this.nDrawWidth = this.nCanvasDrawWidth;

      this.renderBackground(fTrailLengthMetres);                  
      this.renderTrail(jsonPoints, fTrailLengthMetres);
      this.renderMarkers();

      $(window).resize(function() {
        self.render();        
      });    

      this.bRendered = true;

      return this;
    },
    renderBackground: function(fTrailLengthMetres) {
      var nTextHeight = 16;
      
      this.context.beginPath();

      this.context.fillStyle = 'rgba(255,255,255,1)';
      this.context.font = nTextHeight + 'px Arial';
      this.context.textAlign = 'center';
      
      var nMarkerDistance = 1;
      if (fTrailLengthMetres > 20000) {
        nMarkerDistance = 2;
      }
      if (fTrailLengthMetres > 45000) {
        nMarkerDistance = 5;
      }
      if (fTrailLengthMetres > 100000) {
        nMarkerDistance = 10;
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
              
      this.context.lineWidth = 2;
      this.context.strokeStyle = 'rgba(255,255,255,1)';
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
      
      var nMaxPoints = 100;
      var nNumPoints = jsonPoints.length;
      
      // ensure we have enough points
      nNumPoints = (nNumPoints > nMaxPoints) ? nNumPoints : nMaxPoints; 
      var nPointInterval = Math.round(nNumPoints / nMaxPoints)
      
      this.context.beginPath();      
      var nStartX = 0, nStartY = 0;  
      $.each(jsonPoints, function(key, point) {
      	// ignore blank alt
      	if (point.tags.altitude != '') {      		
          nX = nXOffset + self.objTrailMarginRect.left + Math.round(key / self.fXFactor);
          nYPercent = ((point.tags.altitude - Math.round(self.fLowAlt)) / self.fAltRange) * 100;
          nY = nYOffset + self.objTrailMarginRect.top + Math.round((self.nDrawHeight-2) - ((nYPercent * (self.nDrawHeight-2)) / 100));
          
          rem = key % nPointInterval;
          if (rem == 0) {
            self.context.lineTo(nX, nY);            
          }
          if (!nStartX) {
            nStartX = nX;
            nStartY = nY;
          }      		
      	}
      });      
      var nEndX = nX;
      
      // draw last point
      if (jsonPoints.length) {     
        this.context.lineTo(nXOffset + self.objTrailMarginRect.left + self.nDrawWidth, nY);
      }        
      this.context.lineWidth = 4;
      this.context.strokeStyle = this.strLineColour;
      this.context.stroke();      
    },
    renderMarkers: function() {
      if (!this.jsonTrail) {
        return;
      }
      
      var elProfile = $('.profile', this.el);
            
      var nXOffset = (this.nCanvasDrawWidth - this.nDrawWidth) / 2;
      var nYOffset = (this.nCanvasDrawHeight - this.nDrawHeight) / 2;
      
      var viewMediaMarkerView, nX, nY, nYPercent;
      
      for (var nMarker=0; nMarker < this.arrMediaPoints.length; nMarker++) {
        viewMediaMarkerView = this.arrMediaPoints[nMarker];
        if (!this.bRendered) {
          // append marker
          elProfile.append(viewMediaMarkerView.render().el);
        }
        
        nX = nXOffset + this.objTrailMarginRect.left + Math.round(viewMediaMarkerView.pos / this.fXFactor);
        
        nYPercent = ((viewMediaMarkerView.model.get('tags').altitude - Math.round(this.fLowAlt)) / this.fAltRange) * 100;
        nY = nYOffset + this.objTrailMarginRect.top + Math.round((this.nDrawHeight-2) - ((nYPercent * (this.nDrawHeight-2)) / 100));
              
        nX -= 7;
        nY -= 7;
      
        $('.marker', viewMediaMarkerView.el).css('left', nX);
        $('.marker', viewMediaMarkerView.el).css('top', nY);
        
        $('.alt', viewMediaMarkerView.el).css('left', 0);
        $('.alt', viewMediaMarkerView.el).width(nXOffset + this.objTrailMarginRect.left - 20);
        $('.alt', viewMediaMarkerView.el).css('top', nY);        
      }      
    }            
  });

  return TrailAltitudeView;
});
