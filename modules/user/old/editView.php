<?phpclass editView extends AjaxView {	protected $module = 'user';	protected function _prepare() {		$this->setModule();			global $User;		global $Config;		if ( !$User->isLog() ) {			Misc::Load( '/?mod=user' );		} 		$data = $User->getDetails();		$data['open'] = 'edit';		$Config->Templ->Add( 'useredit', $data );    }};