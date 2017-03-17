<?php
class cloneView extends AjaxView {

	protected $module = 'project';

	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( $_GET['id'] );
        if ( empty( $project->properties['id'] ) ) {
                $project = new Project( null, $_GET['id'] );
        }
        
        $newProject = $project->makeClone( $_GET['ref'] );
        if ( $newProject == 'Invalid project') {
        	Misc::Load( '/404' );
        }

        if ( $newProject == 'Clonning locked') {
        	Misc::Load( '/locked' );
        }

        Misc::Load( '/code/' . $newProject->properties['title'] . $ref );
	}
}
  
