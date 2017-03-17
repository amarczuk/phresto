var phpfy = phpfy || new function() {
	var that = this;

	this.addMessage = function( type, message, pers ) {        // success, warning, info, alert, 
        var id = 'message_' + Date.now();
        $('#messageContainer').append( '<div onclick="phpfy.closeMessage(\'' + id + '\');" data-alert class="alert-box '+ type +'" id="' + id + '">' + message + '</div>' );
        if ( !pers ) {
            setTimeout( function() { that.closeMessage( id ); }, 7000 );
        }
        return id;
    }

    this.closeMessage = function( id ) {
        if ( $( '#' + id ).length > 0 ) { 
            $( '#' + id ).remove();
        };
    }

    this.addWait = function( targetid ) {
        $( '#' + targetid ).html( '<div><p class="withpadding text-center"><img src="/modules/code/img/loader.gif"></p></div>' );
    }
    
    this.ajaxForm = function( form ) {
        
        this.ajaxRequest( $( form ).attr( 'action' ), 
                          new FormData(  form ), 
                          $( form ).data( 'target' ), 
                          $( form ).data( 'premessage' ), 
                          $( form ).data( 'postmessage' ), 
                          $( form ).data( 'direct' ) );
    }

    this.ajaxRequest = function( url, postdata, target, premessage, postmessage, direct ) {
        
        var message;
        if ( premessage ) {
            message = {};
            message.id = this.addMessage( 'info', premessage );
            if ( postmessage ) {
                message.post = postmessage;
            }
        }

        $.ajax( url, 
                { method: 'POST',
                 data: postdata,
                 headers: { 'X-Ajax': 1 },
                 cache: false,
                 contentType: false,
                 processData: false,
                 success: function( data ) {
                    if (  direct &&  target && direct == 'yes' ) {
                        $( '#' + target ).html( data );
                        if ( message && message.post ) {
                           that.addMessage( 'success', message.post );  
                        }
                    } else {
                        that.ajaxResponse( data, message );
                    }
                } } );
    }

    this.ajaxResponse = function( data, message ) {
        
        if ( message ) {
            that.closeMessage( message.id );
        }
        
        try {
            var out = JSON.parse( data );
        } catch( e ) {
            that.addMessage( 'alert', 'Request error. Please try again.' );
            return;
        }

        if ( out.error ) {
            for ( var error in out.error ) {
                that.addMessage( 'alert', out.error[error] );
            };
            if ( message ) message.post = '';
        }

        if ( out.change ) {
            for ( var id in out.change ) {
                $( '#' + id ).html( out.change[id] );
            };
        };

        if ( out.prepend ) {
            for ( var id in out.prepend ) {
                $( '#' + id ).prepend( out.prepend[id] );
            };
        };

        if ( out.run ) {
            for ( var code in out.run ) {
                try {
                    eval( out.run[code] );
                } catch(e) {

                }
            };
        }
        
        if ( message && message.post ) {
           that.addMessage( 'success', message.post );  
        }
    }

    this.applyLocks = function( readonly ) {
        if ( readonly ) {
            $( '.editOnly' ).addClass( 'disabled' );
        } else {
            $( '.editOnly' ).removeClass( 'disabled' );
        }
    }

    this.ajaxLink = function( link ) {

        if ( $( link ).hasClass( 'disabled' ) ) {
            return;
        }

        var target = $( link ).data( 'target' );
       
        if ( target != '_run' ) {
            var $target = $( '#' + target );
            this.addWait( target );
        }
        console.log( $( link ).attr( 'href' ) );
        $.ajax( $( link ).attr( 'href' ), 
               { method: 'GET', 
                 headers: { 'X-Ajax': 1 },
                 success: function( data ) {
                    if ( target == '_run' ) {
                        that.ajaxResponse( data );
                    } else {
                        $target.html( data );
                    }

                    $( link ).siblings().filter( 'a.ajaxbutton' ).removeClass( 'active' );
                    $( link ).addClass( 'active' );
                    $( link ).blur();
                } } );
    }

    this.goAwayCookieInfo = function() {
    	$.cookie( 'cookies', 'yes',  { expires: 600, path: '/' } );
    	console.log( 'cookie' );
    	console.log( $.cookie( 'cookies' ) ); 
    }

    this.highlightCode = function() {
        $('.comsCode').each( function() {
            $(this).removeClass( 'comsCode' );
            hljs.highlightBlock( this );
            /*var html = $( this ).html();
            html = '<ol><li>' + html;
            html = html.replace( /\<br\/{0,1}\>/ig, '</li><li>' ) + '</li><ol>';
            $( this ).html( html );*/
        } );
    }

    $( document )
    	.off( 'submit.ajaxform' )
    	.on( 'submit.ajaxform', 
    		 'form[data-target="ajax"]', 
    		 function( e ) { phpfy.ajaxForm( this ); e.preventDefault(); } );

    $( document )
    	.off( 'click.ajaxbutton')
    	.on( 'click.ajaxbutton', 
    		 'a.ajaxbutton', 
    		 function( e ) { 
    		 	e.preventDefault();
                phpfy.ajaxLink( this ); 
                return false;
             } );

    $( function() {
    	if ( !$.cookie( 'cookies' ) || $.cookie( 'cookies' ) != 'yes' ) {
    		phpfy.addMessage( 'info', '<div style="text-align: center"><p><strong>We love cookies and we use them all over the place!</strong></p><a href="/cms/cookies" target="_blank" class="button tiny secondary">I want to read about the cookies</a><br><a href="#" onclick="phpfy.goAwayCookieInfo(); return false;" class="button tiny success">I\'ve got it. Close this message and don\'t show it again.</a></div>', true );
    	}

        if ( $('.undertop.foot').length > 0 ) {
            $foot = $('.undertop.foot');
            if ( $foot.offset().top + $foot.height() < $(window).height() ) {
                $foot.height( $(window).height() - $foot.offset().top - 10 );
            }
        }

    } );

    
}