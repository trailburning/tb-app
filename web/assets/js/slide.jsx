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

	app.Slide = React.createClass({

  getInitialState: function() {
    return {
      focus: this.props.focus
    };
  },

  componentDidUpdate: function(){
  },

  onClick: function(){
    this.props.onSlideClick(this);    
  },

  render: function(){
    var strClass = "slide " + (this.props.focus ? "focus" : "");
    var strTimeAgo = $.format.prettyDate(this.props.created_time);

    return (
      <div className={ strClass }>
        <div className="post">
          <div className="content">
            <div className="image" onClick={this.onClick}><img src={this.props.src}/></div>
            <div className="details clearfix">
              <a href={this.props.user_url} target="_blank"><div className="avatar"><img src={this.props.user_avatar} /></div></a>
              <div className="text">                                    
                <div className="name">{ ((this.props.username).length > 10) ? (((this.props.username).substring(0,10)) + '...') : this.props.username }</div>
                <div className="timeago">{strTimeAgo}</div>
                <div className="caption">{ ((this.props.caption).length > 40) ? (((this.props.caption).substring(0,40)) + '...') : this.props.caption }</div>
              </div>
            </div>
          </div>
          <div className="frame">
              <div className="corner top"></div>
              <div className="corner bottom"></div>
          </div>
        </div>
      </div>
    );
  }        

	});
})();
