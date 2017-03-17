<?php
class newView extends AjaxView {

	protected $module = 'project';
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project();
        $project->create( 'New Sandbox' );

        Misc::Load( '/code/' . $project->properties['title'] );
	}
}
  
