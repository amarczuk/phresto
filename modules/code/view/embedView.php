<?php
class embedView extends AjaxView {
	protected $module = 'code';
	protected function _prepare() {
	    global $Config;
        global $User;
		$this->setModule();
	
        $embed = new Embed( $_GET['id'] );
        $tpl = 'embed_js';

        if ( empty( $embed->id ) ) {
            $tpl = 'embed_js_404';
        }

        $project = new Project( $embed->projectid );

        if ( !$project->isVisible() ) {
            $tpl = 'embed_js_locked';
        }

        
		$Config->Templ->Add( $tpl, [ 'id' => $_GET['id'], 'host' => $_SERVER['HTTP_HOST'] ] );
	}
}
  
