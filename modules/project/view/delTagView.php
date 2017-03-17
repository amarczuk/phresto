<?php

class delTagView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();

        $project = new Project( $_GET['id'] );      

        if ( !$project->isEditable() ) {
                $Config->Templ->Add( 'inline', json_encode( array( 'error' => array( 'You can\'t modify this sandbox' ) ) ) );
                return;
        }

	$tag = new Tag( $_GET['tid'] );
	$project->removeTag( $tag );

        $tpl = new Template();
        $tpl->non_header = true;
        $data = array_merge( $project->getDetails(), $project->getTags() );
        $tpl->add( 'tags',  $data );
        $tags = $tpl->Get();
        unset( $tpl );

        $out['change'] = array( 'tagEditList' => $tags );
        $out['error'] = false;
        
        $Config->Templ->Add( 'inline', json_encode( $out ) );
	}
}
  
