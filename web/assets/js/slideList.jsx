/**
 * @jsx React.DOM
 */
/*jshint quotmark: false */
/*jshint white: false */
/*jshint trailing: false */
/*jshint newcap: false */
/*global React */
var app = app || {};

(function () {
	'use strict';

	app.SlideList = React.createClass({
    getInitialState: function(){
      var slides = this.props.collection.map(function(p, i){
          return { 
              id: p.id, 
              pos: i, 
              src: p.get("image_low_res"),
              caption: p.get("caption"),
              username: p.get("username"),
              user_url: p.get("user_url"),
              user_avatar: p.get("user_avatar"),
              created_time: p.get("created_time"),
              focus: false
          };
      });

      return { slides: slides };
    },

    componentDidMount: function(){
      var self = this;

      this.nCurrSelected = -1;

      this.elMountPoint = $(React.findDOMNode(this));

      this.nContainerWidth = this.elMountPoint.width();

      this.elMountPoint.css("width", $(".slide", this.elMountPoint).first().width() * this.state.slides.length);

      $(window).resize(function() {
        self.ensureSlideInView(self.nCurrSelected);
      });
    },

    componentWillReceiveProps: function(nextProps) {
      this.setFocussedSlide(nextProps.selected);
      this.ensureSlideInView(nextProps.selected);

      this.props.onSlideFocus(nextProps.selected);   
    },

    setFocussedSlide: function(nSelected){      
      if (nSelected == this.nCurrSelected) {
        this.props.onSlideClick(nSelected);     
      }      

      this.nCurrSelected = nSelected;

      var slides = this.state.slides.map(function(p, i){
        p.focus = false;
        if (i == nSelected) {
          p.focus = true;
        }
        return p;
      });
      this.setState({ slides: slides });
    },

    ensureSlideInView: function(nSelected){      
      var elSlideMountPoint;
      if (nSelected == -1) {
        nSelected = 0;
      }
      var elSlideMountPoint = $(".slide", this.elMountPoint).eq(nSelected);

      var nLeft = this.elMountPoint.position().left + elSlideMountPoint.position().left;
      var nSlideWidth = elSlideMountPoint.width();
//      var nWindowWidth = $(window).width();
//      this.nContainerWidth = $(window).width();

      var nMargin = elSlideMountPoint.width() / 2;
      if (nSelected == 0 || (nSelected == this.state.slides.length-1)) {
        nMargin = 0;
      }
      if ((nLeft + nSlideWidth) > this.nContainerWidth) {
        this.elMountPoint.css("left", this.elMountPoint.position().left - ((nLeft + nSlideWidth) - this.nContainerWidth + nMargin));
      }
      if ((nLeft) < 0) {
        this.elMountPoint.css("left", this.elMountPoint.position().left - nLeft + nMargin);
      }      
    },

    slideClick: function(slide){      
      this.setFocussedSlide(slide.props.pos);
      this.props.onSlideFocus(slide.props.pos);   
      this.ensureSlideInView(slide.props.pos);
    },

    nextSlide: function() {        
      var slide = _.filter(this.state.slides, function(slide) {
        return slide.focus === true;
      });

      var nSelected = 0;
      if (slide.length) {
        if (slide[0].pos+1 < this.state.slides.length) {
          nSelected = slide[0].pos+1;
        }
      }
      this.setFocussedSlide(nSelected);      
      this.props.onSlideFocus(nSelected);   
      this.ensureSlideInView(nSelected);
    },

    prevSlide: function() {        
      var slide = _.filter(this.state.slides, function(slide) {
        return slide.focus === true;
      });

      var nSelected = this.state.slides.length-1;
      if (slide.length) {
        if (slide[0].pos-1 >= 0) {
          nSelected = slide[0].pos-1;
        }
      }
      this.setFocussedSlide(nSelected);      
      this.props.onSlideFocus(nSelected);   
      this.ensureSlideInView(nSelected);
    },

    render: function() {        
      var Slide = app.Slide;

      var self = this;
      var slides = this.state.slides.map(function(p, i){
          return <Slide key={p.id} ref={p.id} id={p.id} pos={p.pos} src={p.src} caption={p.caption} username={p.username} user_url={p.user_url} user_avatar={p.user_avatar} created_time={p.created_time} focus={p.focus} onSlideClick={self.slideClick} />
      });

      if(!slides.length){
        slides = <p>Loading images..</p>;
      }

      return (
        <div className="slideContainer">{slides}</div>
      );
    }
    
	});
})();
