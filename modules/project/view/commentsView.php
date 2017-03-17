<?php
class commentsView extends AjaxView {

	protected $module = 'project';
	protected function _prepare() {
	    global $Config;
	    global $User;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );
        
        $data = array_merge( $project->getDetails(), $project->getComments( $_GET['pn'] ), [ 'user_loggedin' => $User->isLog( true ) ] );

		$Config->Templ->Add( 'comments', $data );
	}
}
  
