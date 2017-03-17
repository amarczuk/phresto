<?php

class addTagView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();

        $project = new Project( $_POST['id'] );      

        if ( !$project->isEditable() ) {
                $Config->Templ->Add( 'inline', json_encode( array( 'error' => array( 'You can\'t modify this sandbox' ) ) ) );
                return;
        }

        $tag = new Tag( null, $_POST['name'] );
        if ( empty( $tag->properties['id'] ) ) {
        	$tag->properties['name'] = $_POST['name'];
        	$err = $tag->save();
        	if ( $err !== true ) {
        		$out['error'] = array( $err );
        		$Config->Templ->Add( 'inline', json_encode( $out ) );
        		return;
        	}
        }

        $out['error'] = $project->addTag( $tag );

        if ( $out['error'] === true ) {
        	$tpl = new Template();
        	$tpl->non_header = true;
        	$data = array_merge( $project->getDetails(), $project->getTags() );
        	$tpl->add( 'tags',  $data );
        	$tags = $tpl->Get();
        	unset( $tpl );

        	$out['change'] = array( 'tagEditList' => $tags );
        	$out['run'] = array( '$("#addTagVal").val( "" );' );
        	$out['error'] = false;
        }


        $Config->Templ->Add( 'inline', json_encode( $out ) );
	}
}
  
