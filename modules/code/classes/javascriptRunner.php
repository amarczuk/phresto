<?php
  
class javascriptRunner extends codeRunner {
    
    public function Run() {
        global $Config;                                                              
        
        $code = $this->replaceFileNames();
        
        if ( $this->direct ) {
        	$Config->Templ->Add( 'inline', $code );
        	return;
        } 

        $options = self::getOptions( $this->code->properties['options'] );

        $tmp = explode( "\n", $options['scripts'] );
        $scripts = array();

        if ( $options['jquery'] != 'no' ) {
        	$scripts[] = array( 'url' => $options['jquery'] );
        }

        if ( $options['angular'] != 'no' ) {
            $scripts[] = array( 'url' => $options['angular'] );
        }
        
        foreach ( $tmp as $v ) {
            if ( trim( $v ) != '' )
                $scripts[] = array( 'url' => trim( $v ) );
        }

        $Config->Templ->Add( 'runJavascriptInBody', array( 'filename' => $this->code->properties['name'],
        												   'code' => $code,
        												   'doctype' => $options['html'],
        												   'scripts' => $scripts ) );
    }
    
    public static function getOptions( $options_json ) {
        global $Config;
        
        $conf = $Config->getConfig( 'javascriptrunner' );
        $options = json_decode( $options_json, true );
        if ( empty( $options ) ) $options = array();
        
        $out = array( 'html' => @$options['html'], 
                      'htmls' => array(),
                      'scripts' => @$options['scripts'], 
                      'query' => @$options['query'], 
                      'jqueries' => array(),
                      'jquery' => @$options['jquery'],
                      'angulars' => array(),
                      'angular' => @$options['angular'] );
        
        foreach ( $conf['html'] as $key => $val ) {
            $out['htmls'][] = array( 'name' => $key, 'val'=> $val );
        }

        foreach ( $conf['jquery'] as $key => $val ) {
            $out['jqueries'][] = array( 'name' => $key, 'val'=> $val );
        }
        
        foreach ( $conf['angular'] as $key => $val ) {
            $out['angulars'][] = array( 'name' => $key, 'val'=> $val );
        }

        $out['json'] = $options_json;
        
        return $out;
    }
    
}