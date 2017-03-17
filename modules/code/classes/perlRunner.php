<?php
  
class perlRunner extends phpRunner {

    protected $version;
    protected $options;

    public function Run() {
        global $Config;        
        $this->conf = $Config->getConfig( 'perlrunner' );

        $this->version = 'default';

        $options = json_decode( $this->code->properties['options'], true );
        $this->options = $options;

        $codeDir = $this->createTempCode();
        $out = $this->runCode( $codeDir );    
        $this->removeTempCode( $codeDir );
        
        $out = $this->replaceFileNames( $out );
        $Config->Templ->Add( 'inline', $out );
    }
    
    public static function getOptions( $options_json ) {
        global $Config;
        
        $conf = $Config->getConfig( 'perlrunner' );
        $options = json_decode( $options_json, true );
        if ( empty( $options ) ) $options = array();
        
        $out = array( 'postdata' => @$options['postdata'], 
                      'getdata' => @$options['getdata'], 
                    );
        $out['json'] = $options_json;
        
        return $out;
    }
    
}
