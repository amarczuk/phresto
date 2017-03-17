<?php

class saveSecurityView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();
        
        $project = new Project( $_POST['id'] );      

        if ( !$project->isEditable() || !$project->isOwner() ) {
        	$Config->Templ->Add( 'inline', json_encode( array( 'error' => array( 'You can\'t modify security settings' ) ) ) );
        	return;
        }

        $project->properties['public'] = ( empty( $_POST['public'] ) ) ? 0 : 1;
        $project->properties['noclone'] = ( empty( $_POST['noclone'] ) ) ? 0 : 1;
        $out['error'] = $project->save();


        $Config->Templ->Add( 'inline', json_encode( $out ) );
}
}
  
