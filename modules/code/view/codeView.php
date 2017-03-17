<?php
class codeView extends View {
	protected $module = 'code';
	protected function _prepare() {
	    global $Config;
        global $User;
		$this->setModule();
	
        $project = new Project( null, $_GET['id'] );

        if ( empty( $project->properties['id'] ) ) {
            Misc::Load( '/404' );
        }

        if ( !$project->isVisible() ) {
            Misc::Load( '/locked' );
        }

        $showKey = 0;
        
        if ( empty( $project->properties['owner'] ) && empty( $project->properties['d_seen'] ) && !$User->isLog() ) {
            $showKey = 1;
        }
        
        $project->loadCodes();
        $project->properties['d_seen'] = time();
        $project->properties['views'] = $project->properties['views'] + 1;
        $project->save();

        $cmsCat = new CmsCategory();
        
        $editors = $project->properties['editors'];

        if ( !empty( $_GET['ref'] ) ) {
            $embed = new Embed( $_GET['ref'] );
            if ( !empty( $embed->id ) && !empty( $embed->codes ) && $embed->projectid == $project->properties['id'] ) {
                $editors = $embed->codes;
            }
        }

        $data = array(  'editor' => $project->getEditors(), 
                        'file' => $project->getEditors(), 
                        'activeEditors' => $editors, 
                        'projectId' => $_GET['id'],
                        'sandboxId' => $project->properties['id'],
                        'key' => $project->properties['key'],
                        'showKey' => $showKey,
                        'editable' => ( $project->isEditable() ) ? 'yes' : 'no',
                        'cmscat' => $cmsCat->getAllActive(),
                        'noclone' => $project->properties['noclone'] ? 1 : 0
                      );
                      
        $Config->Templ->cache['cache-control'] = 'no-cache';
        $Config->Templ->AddScript( 'phpfy.js', '', 'main' );
        $Config->Templ->AddScript( 'highlight.pack.js' );
        $Config->Templ->Add( 'project', $data );
        $Config->Templ->AddScript( 'inline_file', 'ace/src-min-noconflict/ace.js', 'kernel' );
        $Config->Templ->AddScript( 'inline_file', 'code.js' );
        $Config->Templ->AddCSS( 'foundation-icons/foundation-icons.css', '', 'kernel' );
        $Config->Templ->AddCSS( 'devicon/devicon.css', '', 'kernel' );
        $Config->Templ->AddCSS( 'code.css' );
        $Config->Templ->AddCSS( 'highlight/solarized_light.css' );
	}
}
  
