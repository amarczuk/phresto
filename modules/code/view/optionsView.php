<?php

class optionsView extends AjaxView {
    
	protected $module = 'code';
    
	protected function _prepare() {
	    global $Config;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );
        $code = new Code( $project->properties['id'], $_POST['codeid'] );   
        
        $codeRunnerClass = $code->properties['type'] . 'Runner';
        if ( !Rejestr::sprawdz( $codeRunnerClass, 'code' ) ) {
            $codeRunnerClass = 'codeRunner';
        }
        
        $data = $codeRunnerClass::getOptions( $code->properties['options'] );
        
        $template = 'runOptions/' . $code->properties['type'];
        if ( !$Config->Templ->Exists( $template ) ) {
        	$template = 'runOptions/default';
        }            
        
		$Config->Templ->Add( $template, $data );                                             
	}
}
  
