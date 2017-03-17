<?php

class deleteView extends AjaxView {
    
	protected $module = 'code';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    
	    $project = new Project( null, $_GET['id'] );

	    if ( !$project->isEditable() ) {
        	$Config->Templ->Add( 'inline', json_encode( array( 'error' => 'You can\'t modify this sandbox' ) ) );
        	return;
        }

        $codeid = $_POST['codeid'];
        $code = new Code( $project->properties['id'], $codeid );
        
        $error = $code->delete();
        if ( $error !== true ) {
            $Config->Templ->Add( 'inline', json_encode( array( 'error' => $error ) ) );
            return;
        }                                               
        
		$Config->Templ->Add( 'inline', json_encode( array( 'success' => 'ok' ) ) );
	}
}
  
