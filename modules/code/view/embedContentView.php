<?php
class embedContentView extends View {
	protected $module = 'code';
	protected function _prepare() {
	    global $Config;
        global $User;
		$this->setModule();
	
        $embed = new Embed( $_GET['id'] );

        if ( empty( $embed->id ) ) {
            Misc::Load( '/404' );
        }

        $project = new Project( $embed->projectid );

        if ( !$project->isVisible() ) {
            Misc::Load( '/locked' );
        }

        $project->loadCodes();

        $embed->views++;
        $embed->save();
        $project->properties['d_seen'] = time();
        $project->save();

        $codes = $embed->getCodes();
        $data = array( 'codesjson' => str_replace( '---textarea---', '</textarea>', json_encode( $codes ) ), 
                       'codes' => $codes, 
                       'projectId' => $project->properties['title'], 
                       'run' => $embed->getRunName(), 
                       'id' => $embed->id,
                       'clone' => $project->noclone ? 'no' : 'yes'

                     );
        
        $Config->Templ->cache['cache-control'] = 'no-cache';
        $Config->Templ->AddScript( 'inline_file', 'ace/src-min-noconflict/ace.js', 'kernel' );
		$Config->Templ->Add( 'embed', $data );
        //$Config->Templ->AddScript( 'inline_file', 'embed.js' );
        $Config->Templ->AddCSS( 'foundation-icons/foundation-icons.css', '', 'kernel' );
        //$Config->Templ->AddCSS( 'embed.css', '' );
	}
}
  
