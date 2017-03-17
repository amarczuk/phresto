<?php

class execView extends AjaxView {
    
	protected $module = 'code';
    
	protected function _prepare() {
	    global $Config;
        $this->setModule();
        
        $pid = ( isset( $_GET['pf_id'] ) ) ? $_GET['pf_id'] : $_GET['id'];
        
        $project = new Project( null, $pid );
        $direct = false;
        if ( isset( $_GET['pf_file'] ) ) {
            $code = new Code( $project->properties['id'], null, $_GET['pf_file'] );
            $direct = true;
        } else {             
            $code = new Code( $project->properties['id'], $_POST['codeid'] );
            $code->properties['code'] = $_POST['code'];
            $code->properties['options'] = json_encode( $_POST['options'] );
            
            if ( $project->isEditable() ) {
                $code->save();
            }
        }

        if ( isset( $_GET['pf_ind'] ) && $_GET['pf_ind'] == '1' ) {
            $direct = false;
        }
        
        $project->loadCodes();
        $codeRunnerClass = $code->properties['type'] . 'Runner';
        if ( !Rejestr::sprawdz( $codeRunnerClass, 'code' ) ) {
            $codeRunnerClass = 'codeRunner';
        }
        $codeRunner = new $codeRunnerClass( $code, $project, $direct );
        $codeRunner->run();
                                                                                                                      
	}
}
  
