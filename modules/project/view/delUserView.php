<?php

class delUserView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();

        $project = new Project( $_GET['id'] );      

        if ( $project->isEditable() && $project->isOwner() ) {
            $project->delUser( $_GET['uid'] );
        }
        

    	$data = array_merge( $project->getDetails(), $project->getUsers() );

        $Config->Templ->Add( 'users', $data );
	}
}
  
