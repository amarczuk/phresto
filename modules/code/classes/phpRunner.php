<?php
  
class phpRunner extends codeRunner {

    protected $version;
    protected $options;

    public function Run() {
        global $Config;        
        $this->conf = $Config->getConfig( 'phprunner' );

        $this->version = 'default';

        $options = json_decode( $this->code->properties['options'], true );
        if ( $options && isset( $options['version'] ) && isset( $this->conf['versions'][$options['version']] ) ) {

            $this->version = $this->conf['versions'][$options['version']];
        }
        $this->options = $options;

        $codeDir = $this->createTempCode();
        $out = $this->runCode( $codeDir );    
        $this->removeTempCode( $codeDir );
        
        $out = $this->replaceFileNames( $out );
        $Config->Templ->Add( 'inline', $out );
    }
    
    protected function createTempCode() {           
        
        $folder = $this->conf[$this->version]['folder'];
        do {
            $tempDir = md5( microtime() . rand( 0, 100000 ) );
        } while ( is_dir( $folder . '/' . $tempDir ) );
        
        mkdir( $folder . '/' . $tempDir );
        
        foreach ( $this->project->codes as $name => $code ) {
            file_put_contents( $folder . '/' . $tempDir . '/' . $name, $this->cleanCode( $code->properties['code'] ) );
        }
        
        return $tempDir;
    }
    
    protected function cleanCode( $code ) {
        return $code;
    }
    
    protected function getDataStr( $data, $array, $ignored = array() ) {
        $out = ( $array ) ? array() : '';
        if ( is_array( $data ) ) {
            foreach ( $data as $key => $value) {   
                if ( in_array( $key, $ignored ) ) continue;
                if ( is_array( $value ) ) {
                    foreach ( $value as $key1 => $value1 ) {
                        if ( $array ) {
                            $out["{$key}[{$key1}]"] = $value1; 
                        } else {
                            $out .= "{$key}[{$key1}]=" . urlencode( $value1 ) . '&';
                        }                                                        
                    }
                } else {
                    if ( $array ) {
                        $out[$key] = $value; 
                    } else {
                        $out .= "{$key}=" . urlencode( $value ) . '&';
                    }
                }
            }
        } else {
            $tmpa = explode( "\n", $data );
            foreach ( $tmpa as $value ) {
                if ( in_array( $key, $ignored ) ) continue;
                $tmpb = explode( '=', $value );
                if ( $array ) {  
                    $out[$tmpb[0]] = $tmpb[1];  
                } else {
                    $out .= trim( $tmpb[0] ) . '=' . urlencode( trim( $tmpb[1] ) ) . '&';
                }                                                                        
            }
        }
        return $out;
    }

    protected function runCode( $dir ) {

        $url = $this->conf[$this->version]['url'] . '/' . $dir . '/' . $this->code->properties['name'];
        $reqType = $_SERVER['REQUEST_METHOD'];
        $reqBody = @file_get_contents('php://input'); 

		$cookie = '';
		foreach ( $_COOKIE as $key => $value) {
			$cookie .= $key . '=' . urlencode( $value );
			$cookie .= ( $key == 'PHPSESSID' ) ? 'n;' : ';';
		}

        if ( $this->direct ) {
            if ( !empty( $_FILES ) ) {
                $topost = $this->getDataStr( $_POST, true );
            } elseif ( !empty( $reqBody ) ) {
                $topost = $reqBody;
            }
            $url .= '?' . $this->getDataStr( $_GET, false, array( 'mod', 'pg', 'pf_id', 'pf_file' ) );
        } else {
            if ( !empty( $this->options['postdata'] ) ) {
                $topost = $this->getDataStr( @$this->options['postdata'], true );
            } else {
                $reqType = 'GET';
            }
            $url .= '?' . $this->getDataStr( @$this->options['getdata'], false );
        }

        $ch = curl_init();    
        
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_COOKIE, $cookie );   // use $_COOKIE
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'phpfy');    
        curl_setopt($ch, CURLOPT_TIMEOUT, 5 );    
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $reqType );    
        // todo: CURLOPT_USERPWD ???
        
        $todelete = array();
        if ( !empty( $_FILES ) ) {
            foreach ( $_FILES as $filename => $file ) {
                if ( is_array( $file['name'] ) ) {
                    foreach ( $file['name'] as $idx => $fname ) {
                        move_uploaded_file( $file['tmp_name'][$idx], 'temp/' . $fname );
                        $topost[$filename . "[{$idx}]"] = '@' . realpath( 'temp/' . $fname ) . ';type=' . $file["type"][$idx];
                        array_push( $todelete, 'temp/' . $fname );
                    }
                } else {
                    move_uploaded_file( $file['tmp_name'], 'temp/' . $file['name'] );
                    $topost[$filename] = '@' . realpath( 'temp/' . $file['name'] ) . ';type=' . $file["type"];
                    array_push( $todelete, 'temp/' . $file['name'] );
                }
            }           
        }        
        
        if ( !empty( $topost ) ) {
            if ( !empty( $_FILES ) ) curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $topost );
        }
        
        $out = curl_exec($ch);
        curl_close($ch);
        
        foreach ( $todelete as $delfile ) {
            @unlink( $delfile );
        }

        if ( $out === false ) {
            $out = LAN_CODE_ERR_TIMEOUT;
        }

        return $out;
    }

    protected function removeTempCode( $dir ) {
    	$folder = $this->conf[$this->version]['folder'];
    	self::removeTmpFile( realpath( $folder . '/' . $dir ) );
    }

    public static function removeTmpFile( $path ) {
    	if ( is_dir( $path ) ) {
    		array_map( 'phpRunner::removeTmpFile', glob( $path . '/*' ) );
    		@rmdir( $path );
    	} else {
    		@unlink( $path );
    	}
    }
    
    public static function getOptions( $options_json ) {
        global $Config;
        
        $conf = $Config->getConfig( 'phprunner' );
        $options = json_decode( $options_json, true );
        if ( empty( $options ) ) $options = array();
        
        $out = array( 'postdata' => @$options['postdata'], 
                      'getdata' => @$options['getdata'], 
                      'versions' => array(),
                      'version' => @$options['version'] );
        
        foreach ( $conf['versions'] as $key => $val ) {
            $out['versions'][] = array( 'name' => 'PHP v' . $key, 'val'=> $key );
        }
        
        $out['json'] = $options_json;
        
        return $out;
    }
    
}
