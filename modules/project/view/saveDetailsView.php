<?php

class saveDetailsView extends AjaxView {
    
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

        $project->properties['description'] = $_POST['description'];
        $project->properties['name'] = $_POST['name'];
        $out['error'] = $project->save();
        
        
        
		$Config->Templ->Add( 'inline', json_encode( $out ) );
	}
}
  
