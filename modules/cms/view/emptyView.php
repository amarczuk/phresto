<?phpclass emptyView extends View {	protected $module = 'main';	protected function _prepare() {			$this->setModule();	
		$this->config->Templ->Add( 'inline', '<p>' . md5( '9009' ) . '</p>' );	}}
  
