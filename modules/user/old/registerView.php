<?php
class registerView extends AjaxView {
	protected $module = 'user';
	protected function _prepare() {
		$this->setModule();
		global $User;
		
        $reg = $User->add( $_POST );
		
        if ( $reg !== true ) {
            $out = array( 'error' => $reg );
		} else {
			$out = array( 'run' => array( 'phpfy.phpfy.loadUser();') );
		}
		
		$this->config->Templ->Add( 'inline', json_encode($out) );
    }
};
