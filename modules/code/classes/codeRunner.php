<?php
  
class codeRunner {
    
    protected $code;
    protected $project;
    protected $conf;
    protected $direct;
    
    public function __construct( $code, $project, $direct = false ) {
        
        if ( strpos( $_SERVER["HTTP_REFERER"], '/' . $_SERVER["HTTP_HOST"] . '/' ) === false ) {
            Misc::Go( '/' );
        }
        
        $this->code = $code;
        $this->project = $project;
        $this->direct = $direct;
    }
    
    public function Run() {
        global $Config;

        $code = $this->replaceFileNames();
        if ( $this->direct ) {
            $Config->Templ->Add( 'inline', $code );
        } else {
            $Config->Templ->AddPR( $code );
        }
    }

    protected function replaceFileNames( $in = null ) {
        $codeval = ( $in !== null ) ? $in : $this->code->properties['code'];
        
        foreach ( $this->project->codes as $name => $code ) {
            $codeval = str_replace( $name, '/code/exec/' . $this->project->properties['title'] . '/' . $name, $codeval );
        }
        
        return $codeval;
    }
    
    public static function getOptions( $options_json ) {
        $options = json_decode( $options_json, true );
        if ( empty( $options ) ) $options = array();
        $options['json'] = $options_json;
        
        return $options;
    }
    
}
