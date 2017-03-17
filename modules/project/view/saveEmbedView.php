<?php

class saveEmbedView extends AjaxView {
    
	protected $module = 'project';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	    $out = array();

        $project = new Project( $_POST['id'] );      

        if ( !$project->isEditable() || !$project->isOwner() ) {
                $Config->Templ->Add( 'inline', json_encode( array( 'error' => array( 'You can\'t change this sandbox' ) ) ) );
                return;
        }

        $embed = new Embed( $_POST['eid'] );
        $embed->name = $_POST['name'];
        $embed->main_code = $_POST['main_code'];
        if ( !empty( $_POST['code'] ) ) {
            $embed->codes = implode( ',', $_POST['code'] );
        } else {
            $embed->codes = '';
        }

        $outp = $project->saveEmbed( $embed );
        $out['error'] = [ $outp ];

        if ( $outp === true ) {
            $out['error'] = false;
            if ( empty( $_POST['eid'] ) ) {
                $tmpl = new Template();
                $tmpl->non_header = true;

                $project->loadCodes();
                $codes = $embed->getCodes();
                $data = $embed->_properties;
                $data['codes'] = $project->getEditors();

                foreach ( $data['codes'] as $key => $value ) {
                    if ( isset( $codes[$value['name']] ) ) {
                        $data['codes'][$key]['in'] = 'y'; 
                    }
                }

                $tmpl->Add( 'editEmbed', $data );
                $out['change'] = [ 'embedEditList' => $tmpl->Get() ];
                unset( $tmpl );
            }
        }

        $Config->Templ->Add( 'inline', json_encode( $out ) );
	}
}
  
