<?php
class securityView extends AjaxView {

	protected $module = 'project';
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );
        
        $data = array_merge( $project->getDetails(), $project->getUsers() );

		$Config->Templ->Add( 'security', $data );
	}
}
  
