<?php
  
class htmlRunner extends codeRunner {
    
    public function Run() {
        global $Config;                                                              
        
        $code = $this->replaceFileNames();
        
        if ( $this->direct || $this->checkHeaders( $code ) ) {
            $Config->Templ->Add( 'inline', $code );
            return;
        } 
        
        $options = self::getOptions( $this->code->properties['options'] );
        $scripts = array();
        $css = array();
        
        if ( $options['jquery'] != 'no' ) {
            $scripts[] = array( 'url' => $options['jquery'] );
        }
        
        if ( $options['angular'] != 'no' ) {
            $scripts[] = array( 'url' => $options['angular'] );
        }

        if ( $options['bootstrap'] != 'no' ) {
            $tmp = explode( '**', $options['bootstrap'] );
            $scripts[] = array( 'url' => $tmp[0] );
            $css[] = array( 'url' => $tmp[1] );
        }

        if ( $options['foundation'] != 'no' ) {
            $tmp = explode( '**', $options['foundation'] );
            $scripts[] = array( 'url' => $tmp[0] );
            $css[] = array( 'url' => $tmp[1] );
        }

        $tmp = explode( "\n", $options['css'] );
        foreach ( $tmp as $v ) {
            if ( trim( $v ) != '' )
                $css[] = array( 'url' => trim( $v ) );
        }

        $tmp = explode( "\n", $options['scripts'] );
        foreach ( $tmp as $v ) {
            if ( trim( $v ) != '' )
                $scripts[] = array( 'url' => trim( $v ) );
        }
        
        $Config->Templ->Add( 'runHtmlWithBody', array( 'code' => $code, 
                                                       'filename' => $this->code->properties['name'],
                                                       'doctype' => $options['html'],
                                                       'css' => $css,
                                                       'post_scripts' => array(),
                                                       'scripts' => $scripts ) );
       
        
    }
    
    private function checkHeaders( $code ) {
    	$c = mb_strtolower( $code );
    	return ( mb_strpos( $c, '<html' ) !== false && 
    		     mb_strpos( $c, '<body' ) !== false );
    }
    
    public static function getOptions( $options_json ) {
        global $Config;
        
        $conf = $Config->getConfig( 'htmlrunner' );
        $options = json_decode( $options_json, true );
        if ( empty( $options ) ) $options = array();
        
        $out = array( 'html' => @$options['html'], 
                      'htmls' => array(),
                      'scripts' => @$options['scripts'], 
                      'css' => @$options['css'], 
                      'query' => @$options['query'], 
                      'jqueries' => array(),
                      'jquery' => @$options['jquery'], 
                      'angulars' => array(),
                      'angular' => @$options['angular'],
                      'foundations' => [],
                      'foundation' => @$options['foundation'],
                      'bootstraps' => [],
                      'bootstrap' => @$options['bootstrap']
                       );
        
        foreach ( $conf['html'] as $key => $val ) {
            $out['htmls'][] = array( 'name' => $key, 'val'=> $val );
        }

        foreach ( $conf['jquery'] as $key => $val ) {
            $out['jqueries'][] = array( 'name' => $key, 'val'=> $val );
        }
        
        foreach ( $conf['angular'] as $key => $val ) {
            $out['angulars'][] = array( 'name' => $key, 'val'=> $val );
        }

        foreach ( $conf['bootstrap'] as $key => $val ) {
            $out['bootstraps'][] = array( 'name' => $key, 'val'=> $val );
        }
        
        foreach ( $conf['foundation'] as $key => $val ) {
            $out['foundations'][] = array( 'name' => $key, 'val'=> $val );
        }
        
        $out['json'] = $options_json;
        
        return $out;
    }
    
}