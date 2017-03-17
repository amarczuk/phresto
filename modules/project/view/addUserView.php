<?php

class addUserView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();

        $project = new Project( $_POST['id'] );      

        if ( !$project->isEditable() || !$project->isOwner() ) {
                $Config->Templ->Add( 'inline', json_encode( array( 'error' => array( 'You can\'t add users to this sandbox' ) ) ) );
                return;
        }

        $out['error'] = $project->addUser( $_POST['email'] );

        
    	$tpl = new Template();
    	$tpl->non_header = true;
    	$data = array_merge( $project->getDetails(), $project->getUsers() );
    	$tpl->add( 'users',  $data );
    	$tags = $tpl->Get();
    	unset( $tpl );

    	$out['change'] = array( 'userEditList' => $tags );
    	$out['run'] = array( '$("#addUserToPr").val( "" );' );


        $Config->Templ->Add( 'inline', json_encode( $out ) );
	}
}
  
