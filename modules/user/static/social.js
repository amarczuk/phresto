var phpfy = phpfy || {};

phpfy.social = phpfy.social || {
	popup: false,
	init: function( className, loginPopup ) {
		if ( this.popup ) {
			try {
				this.popup.close();
			} catch(e) {}
		}
		$( document ).off( 'click.' + className, '.' + className )
					 .on( 'click.' + className, '.' + className, function() {
					     	phpfy.social.login( loginPopup );
					     } );
		$( '.' + className ).addClass( 'loaded' );
	},
	login: function( url ) {
		if ( this.popup ) {
			try {
				this.popup.close();
			} catch(e) {}
		}

		if ( phpfy.Code && phpfy.Code.projectId ) {
			url = url + '?p=' + phpfy.Code.projectId;
		}
		this.popup = window.open( '/' + url, '_blank', 'width=450, height=350' );
	},
	success: function( unlock ) {
		console.log( 'success', unlock );
		if ( phpfy.Code ) {
			phpfy.Code.loadUser( $('#drop_login'), unlock );
		} else {
			location.reload();
		}
	},
	error: function( message ) {
		phpfy.addMessage( 'alert', message );
	}
}

phpfy.social.init( 'fblogin', 'facebook' );
phpfy.social.init( 'googlelogin', 'google' );
phpfy.social.init( 'githublogin', 'github' );
phpfy.social.init( 'linkedinlogin', 'linkedin' );