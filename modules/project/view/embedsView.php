<?php

class embedsView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();

        $project = new Project( $_GET['id'] );      
        $project->loadCodes();

        $data = array_merge( $project->getDetails(), [ 'codes' => $project->getEmbeds() ] );
        $data['url'] = '//' . $_SERVER['HTTP_HOST'];

        $Config->Templ->Add( 'embeds', $data );
	}
}
  
