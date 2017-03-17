<?php

class editEmbedView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();    

        $embed = new Embed( $_GET['eid'] );
        if ( empty( $embed->id ) ) {
            $embed->projectid = $_GET['id'];
        }
        $codes = $embed->getCodes();
        $project = new Project( $_GET['id'] );
        $project->loadCodes();
        $data = $embed->_properties;
        $data['codes'] = $project->getEditors();

        foreach ( $data['codes'] as $key => $value ) {
            if ( isset( $codes[$value['name']] ) ) {
                $data['codes'][$key]['in'] = 'y'; 
            }
        }

        $Config->Templ->Add( 'editEmbed', $data );
	}
}
  
