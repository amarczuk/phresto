<?php
class loginView extends AjaxView {
	protected $module = 'user';
	protected function _prepare() {
		$this->setModule();
		global $User;
	
		if ( !$User->login( $_POST['email'], $_POST['pass'] ) ) {
			$out = array( 'error' => array( 'wrong email or password' ) );
		} else if ( !empty( $_POST['id'] ) ) {
			$project = new Project( null, $_POST['id'] );
			$unlock = ( $project->isEditable() ) ? 'true' : 'false';
			$out = array( 'run' => array( 'phpfy.Code.loadUser( $(\'#drop_login\'), ' . $unlock . ' );') );
		} else {
			$out = array( 'run' => array( 'location.reload();') );
		}
		
		$this->config->Templ->Add( 'inline', json_encode($out) );
    }
};
