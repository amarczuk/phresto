<?php

class mfilesView extends AjaxView {
    
	protected $module = 'code';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );
        $project->loadCodes();
        
        $data = array( 'file' => $project->getEditors() );
        
		$Config->Templ->Add( 'files-mobile', $data );                                             
	}
}
  
