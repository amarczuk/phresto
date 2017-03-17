<?php
class projectView extends AjaxView {

	protected $module = 'project';
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );
        $project->loadCodes();
        
        $data = array_merge( $project->getDetails(), $project->getUsage(), $project->getTags(), $project->getUsers() );
        $data['hideDelete'] = 1;
        
		$Config->Templ->Add( 'project', $data );
	}
}
  
