<?php
class editView extends AjaxView {

	protected $module = 'project';
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );
        $project->loadCodes();
        
        $data = array_merge( $project->getDetails(), $project->getUsage(), $project->getTags(), $project->getUsers() );

		$Config->Templ->Add( 'editProject', $data );
	}
}
  
