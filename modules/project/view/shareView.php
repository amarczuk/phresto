<?php
class shareView extends AjaxView {

	protected $module = 'project';
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );
        $project->loadCodes();
        
        $data = array_merge( $project->getDetails(), [ 'codes' => $project->getEmbeds() ] );
        $data['url'] = '//' . $_SERVER['HTTP_HOST'];

		$Config->Templ->Add( 'share', $data );
	}
}
  
