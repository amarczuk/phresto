<?php

class saveView extends AjaxView {
    
	protected $module = 'code';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    
        $codes = json_decode( $_POST['codes'], true );
        
        $project = new Project( null, $_GET['id'] );

        if ( !$project->isEditable() ) {
        	$Config->Templ->Add( 'inline', 'You can\'t modify this sandbox' );
        	return;
        }

        $project->loadCodes();               
        $project->saveCodes( $codes );                     
        
		$Config->Templ->Add( 'inline', 'ok' );
	}
}
  
