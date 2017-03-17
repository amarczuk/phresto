<?php

class deleteView extends AjaxView {

	protected $module = 'user';

	protected function _prepare() {

		$this->setModule();
	
		global $User;
		global $Config;

		if ( !$User->isLog() ) {
			Misc::Load( '/?mod=user' );
		} 

		if ( $_GET['confirm'] == '1' ) {
			$User->delete();  
			Misc::Load( '/?mod=user' );
		}
		
		$data = array( 'open' => 'delete' );  
		$Config->Templ->Add( 'delete', $data );

    }
};
