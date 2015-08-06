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

	app.DialogDetail = React.createClass({
    componentDidMount: function(){
    },

    componentDidUpdate: function() {
    },

    render: function() {        
      var strTimeAgo = $.format.prettyDate(this.props.created_time);

      return (
        <div className="post-detail-block">
          <div className="dialog-container">
            <div className="image">
            <a href={this.props.link_url} target="_blank">
            <img src={this.props.image_standard_res} />
            </a>
            </div>          
            <div className="container">
              <div className="details">
                <div className="poster clearfix">
                  <a href={this.props.user_url} target="_blank"><div className="tb-avatar"><div className="photo"><img src={this.props.user_avatar} /></div></div></a>
                  <div className="name">{ ((this.props.username).length > 10) ? (((this.props.username).substring(0,10)) + '...') : this.props.username }</div>
                </div>
                <div className="timeago">{strTimeAgo}</div>
                <div className="text">{this.props.caption}</div>
              </div>
            </div>
          </div>
          <div className="prev icon" onClick={this.props.onPrevClick}><i className="fa fa-angle-left"></i></div>
          <div className="next icon" onClick={this.props.onNextClick}><i className="fa fa-angle-right"></i></div>          
        </div>        
      );
    }
    
	});
})();
