var phpfy = phpfy || {};
phpfy.Code = new function() {
	this.activeEditor = {};
	this.editors = {};
	this.codes = {};
	this.visible =[];
    this.lastRunMsg;
    this.projectId;
    this.readonly = false;
    this.outputVisible = true;
	var that = this;
    var inits = false;

    this.init = function() {
        //if ( inits ) return;
        inits = true;
        createEditors();
        if ( Foundation.utils.is_medium_only() || Foundation.utils.is_small_only() ) {
            this.togleOutput();
        }
        this.applyLocks();
    }
    
	var createEditors = function() {
        
        $( '.fileAction' ).hide();
		$( '.codepad_src' ).each( function() {
			var $this = $( this );
			that.codes[ $this.data( 'codeid' ) ] = {};
			var code = that.codes[ $this.data( 'codeid' ) ];
			code.id = $this.data( 'codeid' );
			code.form = $this.attr( 'id' );
			code.mode = $this.data( 'mode' );
			code.name = $this.data( 'name' );
            code.code = $this.val().replace( '---textarea---', '</textarea>' );
            try {
                code.options = JSON.parse( $('#' + code.form + '_options').val() );    
            } catch(e) {
                code.options = {};
            }
            
            $this.val('');
            code.modified = false;
		} );
		
        that.visible = $( '#activeEditors' ).val().split( ',' );
        that.readonly = ( $( '#isEditable' ).val() == 'yes' ) ? false : true;
        that.projectId = $( '#projectId' ).val();
        
        for ( var edId in that.visible ) {
            that.addEditor( that.codes[ that.visible[edId] ] );    
        }

        if ( that.visible.length == 1 && that.visible[0] == '' ) {
            that.resizeEditors();
        }
    }
    
    this.addCode = function( openit ) {
        var code = {};
        code.id = 'new';
        code.form = 'code_' + code.id;
        code.mode = $( '#newMode' ).val();
        code.name = $( '#newName' ).val() + '.' + code.mode.replace( 'javascript', 'js' ).replace( 'perl', 'pl' ).replace( 'python', 'py' );
        code.code = '';
        if ( code.mode == 'php' ) {
            code.code = '<?php\n';
        }
        
        if ( code.mode == 'perl' || code.mode == 'python' ) {
            code.code = 'print "Content-type: text/html\\n\\n";\n';
        }

        code.modified = true;
        
        var mid = that.addMessage( 'info', 'Adding...' );    
        
        $.post( '/code/add/' + $('#projectId').val(), { code: JSON.stringify( code ) }, function( data ) {
            that.closeMessage( mid );
                      
            newcode = JSON.parse( data );
            
            if ( newcode.error ) {
                that.addMessage( 'alert', newcode.error );
                return;
            }
            
            code.id = newcode.id;
            code.form = newcode.form;
            
            that.codes[ code.id ] = code;
        
            $('#codes_form').prepend( '<textarea class="codepad_src" id="'+code.form+'"></textarea>' );
            $('#' + code.form).data( 'mode', code.mode );
            $('#' + code.form).data( 'name', code.name );
            $('#' + code.form).data( 'codeid', code.id );     
            
            $.get( '/code/files/' + $('#projectId').val(), function( data ) {
                $( '#fileMenuContent').html( data );
                that.addMessage( 'success', 'File ' + code.name + ' has been added to the project' );
                if ( openit ) {
                    $('#pop_add_file').foundation('reveal', 'close');
                    if ( that.activeEditor && that.activeEditor.id && that.activeEditor.cid ) {
                        that.changeEditor( code.id );
                    } else {
                        that.addEditor( code );
                    }
                }
            });

            $.get( '/code/mfiles/' + $('#projectId').val(), function( data ) {
                $( '#mfileMenuContent').html( data );
            });
            
        } );
        
    }
    
    this.saveCodes = function( codeIds ) {
        if ( this.activeEditor && this.activeEditor.cid && this.codes[this.activeEditor.cid].modified ) {
            this.codes[this.activeEditor.cid].code = this.editors[this.activeEditor.id].editor.getSession().getValue();
        }

        var toSave = { codes: [], visible: [] };
        var id, cid;

        for ( id in codeIds ) {
            toSave.codes.push( this.codes[codeIds[id]] );
        }
        for ( id in this.editors ) {
            var cid = $( '#' + id ).data( 'for' );
            toSave.visible.push( cid );
        }
        
        var mid = that.addMessage( 'info', 'Saving...' );

        $.post( '/code/save/' + $('#projectId').val(), { codes: JSON.stringify( toSave ) }, function( data ) {
            that.closeMessage( mid );
            
            if ( data != 'ok' ) {
                that.addMessage( 'alert', data );
                return;
            }
            for ( id in codeIds ) {
                that.codes[codeIds[id]].modified = false;
            }                        
            var msg = 'File saved';     
            if ( codeIds.length > 1 ) msg = codeIds.length + ' files saved';             
            that.addMessage( 'success', msg );

        } );
    }
    
    this.getActiveCode = function() {
        if ( this.activeEditor && this.activeEditor.cid ) {
            return this.codes[ this.activeEditor.cid ];
        } 

        for ( id in this.codes ) {
            return this.codes[id];
        } 
    }

    this.addEditor = function( code ) { 
        if ( !code ) return;   
        var orgId = code.form;
        var id = orgId + '_' + Date.now();  
        
        $( '#output_container' ).before( '<div id="' + id + '" data-for="' + code.id + '" class="codepad small-6 columns"></div>' );
        $( '#output_container' ).before( '<div id="' + id + '_lab" data-for="' + id + '" class="codepadLabel">' + code.name + '</div>' );
        
        var editor = { editor: ace.edit( id ), codes: {} };
        editor.editor.setTheme("ace/theme/monokai");
        var mode = code.mode;
        if ( mode == 'pm' ) mode = 'perl';
        editor.editor.getSession().setMode("ace/mode/" + mode );
        editor.editor.$blockScrolling = Infinity;
        editor.editor.getSession().setValue( code.code );
        editor.editor.renderer.scrollBarV.on( 'scroll', function() {
            var code = that.codes[ $( '#' + id ).data( 'for' ) ];
            if ( code ) {
                editor.codes[code.id] = { cursor: editor.editor.getCursorPosition(), row: editor.editor.getFirstVisibleRow() };
            };
        });
        if ( this.readonly ) {
            editor.editor.setReadOnly( true );
        };

        editor.editor.on( 'change', function() {
            var code = that.codes[ $( '#' + id ).data( 'for' ) ];
            if ( code ) {
                code.modified = true;
            }
        } ); 
        editor.editor.on( 'blur', function() { 
            $( '#' + id + '_lab' ).fadeIn();
            eddiv = $( '#' + id );
            $( '#' + id + '_lab' ).offset( { top: eddiv.offset().top, left: eddiv.offset().left } );
            $( '#' + id + '_lab' ).width( eddiv.width() - 20 );
            $( '#' + id + '_lab' ).height( eddiv.height() - 20 );
            var code = that.codes[ $( '#' + id ).data( 'for' ) ];
            if ( code ) {
                if ( code.modified ) {
                    code.code = editor.editor.getSession().getValue();                    
                }                                          
                editor.codes[code.id] = { cursor: editor.editor.getCursorPosition(), row: editor.editor.getFirstVisibleRow() };
            } 
            
        } ); 

        this.editors[id] = editor;

        that.resizeEditors(); 
        return id;
    }

    this.togleOutput = function() {
        this.outputVisible = !this.outputVisible;
        if ( this.outputVisible ) {
            $( '.togleoutput' ).addClass( 'active' );
        } else {
            $( '.togleoutput' ).removeClass( 'active' );
        }
        $( '#output_container' ).toggle();
        this.resizeEditors();
    }

    this.showActiveCodepad = function() {
        var cp;

        if ( $( '.codepad.active' ).length ) {
            cp = $( '.codepad.active' );
            cp.show();
            return true;
        }

        if ( $( '.codepad' ).length ) {
            cp = $( $( '.codepad' )[0] );
            cp.show();
            $( '#' + cp.attr( 'id' ) + '_lab' ).show();
            return true;
        }

        return false;

    }

    this.resizeEditors = function() {                          
        var edcount = $( '.codepad' ).length;
        if ( this.outputVisible ) edcount++;

        if ( Foundation.utils.is_medium_only() || Foundation.utils.is_small_only() ) {
            edcount = 1;
            $( '.codepad' ).hide();
            $( '.codepadLabel' ).hide();

            if ( !this.outputVisible ) {
                if ( !this.showActiveCodepad() ) {
                    this.togleOutput();
                    return;
                }
            }
        } else {
            $( '.codepad' ).show();
            $( '.codepadLabel' ).show();
        }

        var rowcount = Math.round( edcount / 2 ); 
        var h = $( window ).height() - $( '#topMenu' ).outerHeight();     
        var nh = h / rowcount;

        $( '.codepad' ).height( nh - 4 );
        if ( this.outputVisible ) {
            $( '.codepad' ).removeClass( 'small-12' ).addClass( 'small-6' );
            $( '#output_container' ).height( nh - 4 );
            if ( $( '.codepad' ).length % 2 == 0 || Foundation.utils.is_medium_only() || Foundation.utils.is_small_only() ) {
                $( '#output_container' ).removeClass( 'small-6' ).addClass( 'small-12' ); 
            } else {
                $( '#output_container' ).removeClass( 'small-12' ).addClass( 'small-6' );       
            }
        } else {
            if ( Foundation.utils.is_medium_only() || Foundation.utils.is_small_only() ) {
                $( '.codepad' ).removeClass( 'small-6' ).addClass( 'small-12' );
            } else if ( edcount % 2 == 0 ) {
                $( '.codepad' ).removeClass( 'small-12' ).addClass( 'small-6' );       
            } else {
                $( '.codepad' ).last().removeClass( 'small-6' ).addClass( 'small-12' );       
            }
        }

        var eddiv;
        for ( var ed in this.editors ) {
            this.editors[ed].editor.resize();
            eddiv = $( '#' + ed );
            $( '#' + ed + '_lab' ).offset( { top: eddiv.offset().top, left: eddiv.offset().left } );
            $( '#' + ed + '_lab' ).width( eddiv.width() - 20 );
            $( '#' + ed + '_lab' ).height( eddiv.height() - 20 );
        }        
        
        this.resizeOutput();         
    }

    this.setReadOnly = function() {
        for ( var ed in this.editors ) {
            this.editors[ed].editor.setReadOnly( this.readonly );
        }       
        this.applyLocks();
    }

    this.resizeDrops = function( drop, event ) {
        var id = $( drop ).attr( 'id' );
        var resizeTimeout;
        if ( Foundation.utils.is_small_only() ) {
            $( drop ).css( 'top', '5px' );
        }
        var autoD = $('#' + id + ' .autoDropContent');  
        if ( autoD.length == 0 ) return;  
        var dropH;
        if ( event == 'open' || event == 'opened' ) {                  
            dropH = $( window ).height() - autoD.position().top - $( drop ).position().top - 10;     
            autoD.nextAll().each( function() {                              
                                    dropH -= $( this ).outerHeight();
                                } );   

            if ( event == 'open' ) {
                $( window ).on( 'resize.drops_' + id, function() {
                    if ( !Foundation.utils.is_small_only() ) {
                        $(document).foundation('dropdown', 'close', $('.f-dropdown'));
                        return;
                    }
                    if ( resizeTimeout ) {
                        clearTimeout( resizeTimeout );
                    }
                    resizeTimeout = setTimeout( function() {
                        phpfy.Code.resizeDrops( drop, 'opened' );
                    }, 200 );
                } );
            } 
        } else {
            dropH = 10;
             $( window ).off( 'resize.drops_' + id );
        }
        
        if ( !autoD.hasClass( 'max' ) ) {
            var contentH = 0;
            autoD.children().each( function() { contentH += $( this ).height(); } );
            if ( contentH < dropH ) {
                dropH = contentH;
            }
        }
        
        autoD.height( dropH );
        this.applyLocks();
                                    
    }
    
    this.activateEditor = function( id ) {
        var cid = $( '#' + id ).data( 'for' );   
        var loadOptions = false;   

        if ( this.activeEditor && this.activeEditor.id && this.activeEditor.id != id ) {
            $( '#' + this.activeEditor.id ).removeClass( 'active' ); 
            loadOptions = true;
        } else if ( !this.activeEditor || !this.activeEditor.id || this.activeEditor.loadoptions ) {
            loadOptions = true;
            this.activeEditor.loadoptions = null;
        }

        $( '#' + id ).addClass( 'active' );                                                    
        var editor = this.editors[id];
        editor.editor.setValue( this.codes[cid].code );

        if ( editor.codes[cid] && editor.codes[cid].cursor ) {
            editor.editor.navigateTo( editor.codes[cid].cursor.row, editor.codes[cid].cursor.column );
            editor.editor.scrollToRow( editor.codes[cid].row );
        } else {
            editor.editor.navigateTo( 0, 0 );
        }
        
        this.activeEditor = { id: id, cid: cid };
        $( '.fileSelector span' ).html( this.codes[ cid ].name );
        $( '.fileAction' ).show();
        editor.editor.focus();
        $( '#' + id + '_lab' ).fadeOut();

        if ( loadOptions ) {
            $.post( '/code/options/' + $('#projectId').val(), { codeid: cid }, function( data ) {
                $( '#runOptions' ).html( data );
                var options = that.codes[cid].options;
                for( var opt in options ) {
                    if ( $( '#run_' + opt ).length > 0 ) {
                        $( '#run_' + opt ).val( options[opt] );
                    }
                }
            } );
        }

        if ( this.outputVisible && ( Foundation.utils.is_medium_only() || Foundation.utils.is_small_only() ) ) {
            this.togleOutput();
        }

    }

    this.closeEditor = function( id ) {
        var editor = this.editors[ id ].editor;
        editor.destroy();
        delete this.editors[ id ];
        
        var obj = $( '#' + id ).remove();
        obj = $( '#' + id + '_lab' ).remove();
        
        if ( this.activeEditor && this.activeEditor.id && this.activeEditor.id == id ) {
            $( '.fileSelector span' ).html( 'Click to open/add file' );
            this.activeEditor = {};
            $( '.fileAction' ).hide();
        }                    
        this.resizeEditors();      
    }

    this.closeActive = function() {
        if ( this.activeEditor && this.activeEditor.id ) {
            this.closeEditor( this.activeEditor.id );
        }
    }
    
    this.saveActive = function() {
        if ( this.readonly ) return;
        if ( this.activeEditor && this.activeEditor.cid ) {
            this.saveCodes( [ this.activeEditor.cid ] );
        }
    }

    this.delActive = function() {
        if ( this.readonly ) return;
        if ( !this.activeEditor || !this.activeEditor.cid ) {
            this.addMessage( 'warning', 'No file selected.' );
            return;
        }

        var cid = this.activeEditor.cid;
        var name = this.codes[ cid ].name;
        var mid = this.addMessage( 'info', 'Deleting ' + name + '...' );

        $.post( '/code/delete/' + $('#projectId').val(), { codeid: cid }, 
                function( data ) {
                                             
                    out = JSON.parse( data );
            
                    if ( out.error ) {
                        that.closeMessage( mid );
                        that.addMessage( 'alert', out.error );
                        return;
                    }

                    var tmp = $( '#' + that.codes[cid].form ).remove();

                    delete that.codes[cid];

                    $( '.codepad[data-for="' + cid + '"]' ).each( function() {
                            var id = $( this ).attr( 'id' );
                            var edit = that.editors[ id ];
                            var rmv = true;
                            var ecid;

                            console.log(edit.codes);
                            for ( ecid in edit.codes ) {
                                if ( ecid != cid && that.codes[ecid] ) {
                                    that.changeEditor( ecid, id );
                                    rmv = false;
                                    break;
                                }
                            }
                            
                            
                            if ( rmv ) {
                                that.closeEditor( id );
                            }
                        } );
                        
                    that.closeMessage( mid );
                    
                    $.get( '/code/files/' + $('#projectId').val(), function( data ) {
                        $( '#fileMenuContent').html( data );
                        that.addMessage( 'success', 'File ' + name + ' has been removed from the project' );
                    });

                    $.get( '/code/mfiles/' + $('#projectId').val(), function( data ) {
                        $( '#mfileMenuContent').html( data );
                    });

              } );
    }

    this.runActive = function() {
        if ( !this.activeEditor || !this.activeEditor.cid ) {
            this.addMessage( 'warning', 'No file selected.' );
            return;
        }
        
        $("#outputFrame").css( { height: '10px', width: $( '#output_container' ).innerWidth() + 'px' } );
        var code = this.codes[ this.activeEditor.cid ];
        code.code = this.editors[ this.activeEditor.id ].editor.getSession().getValue();
        $( '#runCode' ).val( code.code );
        $( '#runCodeid' ).val( code.id );
        $( '#runForm' ).submit();
        that.lastRunMsg = that.addMessage( 'info', 'Running ' + code.name + '...' );
        
    }

    this.downloadActive = function() {
        if ( !this.activeEditor || !this.activeEditor.cid ) {
            this.addMessage( 'warning', 'No file selected.' );
            return;
        }

        location.href = '/?mod=code&pg=download&cid=' + this.activeEditor.cid;

        this.addMessage( 'info', 'Download will start in a few seconds...' );
    }
    
    this.saveAll = function() {
        if ( this.readonly ) return;
        var code;
        var save = [];
        for ( code in this.codes ) {
            if ( this.codes[code].modified ) {
                save.push( this.codes[code].id );
            }
        }
        
        if ( save.length > 0 ) {
            this.saveCodes( save );
            return;
        }                                
        this.addMessage( 'warning', 'No code changes.' );
    }

    this.changeEditor = function( codeid, eid ) {

        if ( !this.activeEditor || !this.activeEditor.cid ) {
            this.activeEditor = false;
            for ( var ed in this.editors ) {
                this.activeEditor = { id: ed, cid: -1 };
                break;
            }  
            if ( !this.activeEditor ) {
                eid = this.addEditor( this.codes[codeid] );
                this.activeEditor = { id: eid, cid: codeid };
            }
        } 

        if ( ( this.activeEditor && this.activeEditor.cid && this.activeEditor.cid != codeid ) || eid ) {
            var editor = ( eid ) ? this.editors[ eid ] : this.editors[ this.activeEditor.id ];
            var edid = ( eid ) ? eid : this.activeEditor.id;
            var code = this.codes[ codeid ];
                      
            $( '#' + edid ).attr( 'data-for', code.id );
            $( '#' + edid ).data( 'for', code.id );
            var mode = code.mode;
            if ( mode == 'pm' ) mode = 'perl';
            editor.editor.getSession().setMode( "ace/mode/" + mode );
            
            this.activeEditor.loadoptions = true;
            this.activateEditor( edid );
            $( '.fileSelector span' ).html( code.name );
            $( '#' + edid + '_lab' ).html( code.name );
            this.resizeEditors();
            
        } else {
            this.activateEditor( this.activeEditor.id );
        }        
    }
    
    this.addMessage = function( type, message, pers ) {        // success, warning, info, alert, 
        return phpfy.addMessage( type, message, pers );
    }

    this.closeMessage = function( id ) {
        phpfy.closeMessage( id );
    }
	
    this.afterRun = function() {

        if ( this.lastRunMsg ) {
            this.closeMessage( this.lastRunMsg );
            this.lastRunMsg = null;
            this.addMessage( 'success', 'Complete. Please check the output.')
        }

        // todo: close popover
        
        $('#drop_run').foundation( 'dropdown', 'close', $('#drop_run') ); 
    }

    this.resizeOutput = function() {
        var frame = document.getElementById( 'outputFrame' );
        var c = (frame.contentWindow || frame.contentDocument);
        var d;
        var oc = $('#output_container');
        if (c.document) d = c.document;
        var ih = $(d).outerHeight();
        var iw = $(d).outerWidth();
        if ( ih <  oc.innerHeight() - 10 ) ih = oc.innerHeight() - 10;
        if ( iw < oc.innerWidth() - 10 ) iw = oc.innerWidth() - 10;
        $(frame).css({
            height: ih,
            width: iw
        });
    }

    this.updateOption = function( element ) {
        if ( !this.activeEditor || !this.activeEditor.id || !this.activeEditor.cid ) return;
        
        var optname = $( element ).attr( 'id' ).replace( 'run_', '' );

        if ( !this.codes[this.activeEditor.cid].options ) {
            this.codes[this.activeEditor.cid].options = {};
        }

        this.codes[this.activeEditor.cid].options[optname] = $( element ).val();
    }
    
    this.loadUser = function( drop, unlock ) {

        if ( typeof unlock != 'undefined' ) {
            this.readonly = !unlock;
            this.setReadOnly();
        }

        this.addWait( 'loginContent' );
        this.resizeDrops( drop, 'open' ); 
        var url = '/?mod=user&id=' + this.projectId;
        var last = $( drop ).data( 'lastopen' );
        
        if ( last && $( last ).attr( 'href' ).indexOf('delete') == -1 ) {
            url = $( last ).attr( 'href' );
        }
        
        $.get( url, 
                function( data ) {
                    $( '#loginContent' ).html( data );
                    that.resizeDrops( drop, 'open' );
                    if ( $('#loginFormDiv').length > 0 ) {
                        $( '#drop_login a.dropfoot.ajaxbutton' ).removeClass( 'active' ).addClass( 'disabled' );
                    } else {
                        $( '#drop_login a.dropfoot.ajaxbutton' ).removeClass( 'active' ).removeClass( 'disabled' );
                        if ( last ) {
                            $( last ).addClass( 'active' );
                        }else {
                            $( $( '#drop_login a.dropfoot.ajaxbutton' )[0] ).addClass( 'active' );
                        }
                        
                    }
                } );
    }

    this.loadAjaxDrop = function( drop ) {
        
        var last = $( drop ).data( 'lastopen' );
        if ( last ) {
            this.ajaxLink( last, true );
            return;
        }
        
        this.ajaxLink( $( drop ).find('a.dropfoot')[0], true );
    }

    this.ajaxLink = function( link, resizeWait ) {

        if ( $( link ).hasClass( 'disabled' ) ) {
            return;
        }

        var target = $( link ).data( 'target' );

        var drop = $( link ).data( 'drop' );
        var $drop;
        
        if ( drop ) {
            $drop = $( '#' + drop );
        }

        if ( target != '_run' ) {
            var $target = $( '#' + target );
            this.addWait( target );
            if ( resizeWait && drop ) {
                this.resizeDrops( $drop, 'open' );
            }
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

                    if ( drop ) {
                        that.resizeDrops( $drop, 'open' );
                        $drop.data( 'lastopen', link );
                    } else {
                        that.applyLocks();
                    }

                    $( link ).siblings().filter( 'a.ajaxbutton' ).removeClass( 'active' );
                    $( link ).addClass( 'active' );
                    $( link ).blur();
                } } );
    }

    this.addWait = function( targetid ) {
        phpfy.addWait( targetid );
    }
    
    this.ajaxForm = function( form ) {
        phpfy.ajaxForm( form );
        this.applyLocks();
    }

    this.ajaxResponse = function( data, message ) {
        phpfy.ajaxResponse( data, message );
        this.applyLocks();
    }

    this.applyLocks = function() {
        phpfy.applyLocks( this.readonly );
    }

}


$( function() {
        $(document).foundation({ 
            dropdown: {
                opened: function() { 
                                     if ( $( this ).attr( 'id' ) == 'drop_login' ) {
                                        phpfy.Code.loadUser( this );
                                     } else if( $( this ).hasClass( 'ajaxdrop' ) ) {
                                        phpfy.Code.loadAjaxDrop( this );
                                     } else {                                
                                        phpfy.Code.resizeDrops( this, 'open' );  
                                     }
                                   },
                closed: function() { phpfy.Code.resizeDrops( this, 'close' );  },
              },
              offcanvas : {
                // Sets method in which offcanvas opens.
                // [ move | overlap_single | overlap ]
                open_method: 'move', 
                close_on_click : true
              } } );
        phpfy.Code.init();                                   
                            
        $( document ).off( 'submit.ajaxform' ).on( 'submit.ajaxform', 'form[data-target="ajax"]', function( e ) { phpfy.Code.ajaxForm( this ); e.preventDefault(); } );

        $( window ).on( 'resize.editors', function() { phpfy.Code.resizeEditors() } );

        $( document ).on( 'click.filechange', '.filechange', function() { phpfy.Code.changeEditor( $( this ).data( 'codeid' ) ); } );
        $( document ).on( 'click.activeditor', '.codepadLabel', function() { phpfy.Code.activateEditor( $( this ).data( 'for' ) ); } );

        $( document ).on( 'click.neweditor', '.neweditor', function() { phpfy.Code.addEditor( phpfy.Code.getActiveCode() ); } );
        $( document ).on( 'click.togleoutput', '.togleoutput', function() { phpfy.Code.togleOutput(); } );
        $( document ).on( 'click.close', '#closeEditor', function() { phpfy.Code.closeActive(); } );

        $( document ).on( 'click.add', '#addFileSubmit', function() { phpfy.Code.addCode(); } );
        $( document ).on( 'click.addno', '#addAndOpenFileSubmit', function() { phpfy.Code.addCode( true ); } );
        $( document ).on( 'click.addclose', '#closeFileSubmit', function() { $('#pop_add_file').foundation('reveal', 'close'); } );
        
        $( document ).on( 'click.del', '#yesDelFile', function() { phpfy.Code.delActive(); } );
        $( document ).on( 'click.run', '.runit', function() { phpfy.Code.runActive(); } );
        $( document ).on( 'click.download', '.fileDownload', function() { phpfy.Code.downloadActive(); } );
        $( document ).on( 'change.options', '.runoption', function() { phpfy.Code.updateOption( this ); } );
        
        $( document )
            .off( 'click.ajaxbutton')
            .on( 'click.ajaxbutton', 
                 'a.ajaxbutton', 
                 function( e ) { 
                    e.preventDefault();
                    phpfy.Code.ajaxLink( this ); 
                    return false;
                 } );


        $( document ).on( 'click.save', '.saveActive', function() { phpfy.Code.saveActive(); } );
        $( document ).on( 'click.saveall', '.saveAll', function() { phpfy.Code.saveAll(); } );
        $( document ).on( 'click.closedrop', '.closeDrop', function() { 
            $(document).foundation('dropdown', 'close', $('.f-dropdown')); 
        } );

        $("#outputFrame").load(function() { phpfy.Code.resizeOutput(); phpfy.Code.afterRun(); } );
        
        $(window).bind('keydown', function(event) {
                                    if (event.ctrlKey || event.metaKey) {
                                        switch (String.fromCharCode(event.which).toLowerCase()) {
                                        case 's':
                                            event.preventDefault();
                                            phpfy.Code.saveActive();
                                            break;
                                        case 'r':
                                            event.preventDefault();
                                            phpfy.Code.runActive();
                                            break;
                                        
                                        }
                                    }
                                  });
} );