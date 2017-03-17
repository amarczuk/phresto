<?php

class addView extends AjaxView {
    
	protected $module = 'code';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    
        $code = json_decode( $_POST['code'], true );
        
        $project = new Project( null, $_GET['id'] );

        if ( !$project->isEditable() ) {
            $Config->Templ->Add( 'inline', json_encode( array( 'error' => 'You can\'t modify this sandbox' ) ) );
            return;
        }

        $project->loadCodes();               
        $error = $project->addCode( $code['name'], $code['mode'], $code['code'] );  
        if ( $error !== true ) {
            $Config->Templ->Add( 'inline', json_encode( array( 'error' => $error ) ) );
            return;
        }
        $editor = $project->getEditor( $code['name'] );
        
		$Config->Templ->Add( 'inline', json_encode( array( 'id' => $editor['codeid'], 'form' => $editor['id'] ) ) );
	}
}
  
