<?php

class downloadView extends AjaxView {
    
	protected $module = 'code';
    
	protected function _prepare() {
	    global $Config;
        $this->setModule();
        
        $code = new Code( null, $_GET['cid'] );
        
        $Config->Templ->Add( 'inline', $code->getCode(), array() );
        header( 'Content-Type: text/plain' );
        header( "Content-Disposition: attachment; filename={$code->properties['name']}" );
                                                                                                                      
	}
}
  
