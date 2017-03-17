<?php

class delEmbedView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();

        $project = new Project( $_GET['id'] );      
        $project->loadCodes();

        if ( !$project->isEditable() || !$project->isOwner() ) {
            $Config->Templ->AddScript( 'inline_js', 'phpfy.addMessage( "alert", "You can\'t modify this sandbox" );' );
        } else {
            $embed = new Embed( $_GET['eid'] );
            if ( $embed->delete() === true ) {
                $Config->Templ->AddScript( 'inline_js', 'phpfy.addMessage( "success", "Code deleted" );' );
            }
        }

        $data = array_merge( $project->getDetails(), [ 'codes' => $project->getEmbeds() ] );
        $data['url'] = '//' . $_SERVER['HTTP_HOST'];

        $Config->Templ->Add( 'embeds', $data );
	}
}
  
