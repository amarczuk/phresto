<?php
class showCommentsView extends AjaxView {

	protected $module = 'project';
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( $_GET['id'] );
        
        $data = array_merge( $project->getDetails(), $project->getComments( $_GET['pn'] ) );

		$Config->Templ->Add( 'commentContents', $data );
	}
}
  
