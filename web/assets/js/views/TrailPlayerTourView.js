L.Map.prototype.setViewFIXED = function(center, zoom, options) {
  zoom = zoom === undefined ? this._zoom : this._limitZoom(zoom);
  center = this._limitCenter(L.latLng(center), zoom, this.options.maxBounds);
  options = options || {};

//    this.stop();
/* replaced */
  L.Util.cancelAnimFrame(this._flyToFrame);
  if (this._panAnim) {
    this._panAnim.stop();
  }
/* replaced */

  if (this._loaded && !options.reset && options !== true) {

    if (options.animate !== undefined) {
      options.zoom = L.extend({animate: options.animate}, options.zoom);
      options.pan = L.extend({animate: options.animate, duration: options.duration}, options.pan);
    }

    // try animating pan or zoom
    var animated = (this._zoom !== zoom) ?
      this._tryAnimatedZoom && this._tryAnimatedZoom(center, zoom, options.zoom) :
      this._tryAnimatedPan(center, options.pan);

    if (animated) {
      // prevent resize handler call, the view will refresh after animation anyway
      clearTimeout(this._sizeTimer);
      return this;
    }
  }

  // animation didn't start, just reset the map view
  this._resetView(center, zoom);

  return this;
}

define([
  'underscore', 
  'backbone',
  'views/trailplayertour/JourneyView'
], function(_, Backbone, JourneyView){
  var AppView = Backbone.View.extend({
    initialize: function(){
      // Prague to London
//      this.journeyView1 = new JourneyView({ el: '.journey-view.drive', nZoom: 6, nLabelWidth: 100, strType: 'drive' });      
      // Melbourne to Mount Buller
      this.journeyView1 = new JourneyView({ el: '.journey-view.drive', nZoom: 10, nLabelWidth: 160, nRoutePoints: 200, strType: 'drive' });      
      this.journeyView1.render();

      this.journeyView2 = new JourneyView({ el: '.journey-view.hike', nZoom: 14, nLabelWidth: 120, nRoutePoints: 300, strType: 'hike' });      
      this.journeyView2.render();
    }

  });

  return AppView;
});
