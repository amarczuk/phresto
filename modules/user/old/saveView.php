<?php
class saveView extends AjaxView {
	protected $module = 'user';
	protected function _prepare() {
		$this->setModule();
		global $User;
		                    
        $data = $_POST;
        $data['reg'] = 'ok';
        if ( $data['pass'] == '' ) {
            $data['nopass'] = true;
        }                   
        
        if ( $_FILES['pic'] ) {
        	$data['pic'] = $_FILES['pic'];
        }
                            
        $reg = $User->update( $data );
		
        if ( $reg !== true ) {
            $out = array( 'error' => $reg );
		} else {
			$out = array( 'run' => array( 'phpfy.addMessage( "success", "User details updated" );' ) );
		}
		
		$this->config->Templ->Add( 'inline', json_encode($out) );
    }
};
