<?php

namespace Phresto;

require_once( __DIR__ . '/Config.php' );

class Utils {

	public static function autoload( $className ) {
		$base = __DIR__ . '/../../';
		$path = explode( '\\', trim( $className, '\\' ) );
		$modules = Config::getConfig( 'modules' );

		if ( $path[0] != 'Phresto' ) return;
		if ( in_array( $path[1], [ 'Interf', 'Exception' ] ) ) {
			$file = $base . 'kernel/' . $path[1] . '/' . $path[2] . '.php';
		} else if ( $path[1] == 'Modules' ) {

			foreach ( $modules as $module => $files ) {
				if ( !empty( $path[3] ) && !empty( $files[$path[2]] ) ) {
					if ( in_array( $path[3] . '.php', $files[$path[2]] ) ) {
						$file = $base . '/modules/' . $module . '/' . mb_strtolower( $path[2] ) . '/' . $path[3] . '.php';
						break;
					}
				} else if ( !empty( $files['class'] ) && in_array( $path[2], $files['class'] ) ) {
					$file = $base . '/modules/' . $module . '/class/' . $path[2] . '.php';
					break;
				}
			}
			
		} else {
			$file = $base . '/kernel/class/' . $path[1] . '.php';
		}

		if ( file_exists( $file ) ) {
			require_once( $file );
		}
	}

	public static function registerAutoload() {
		spl_autoload_register( 'Phresto\\Utils::autoload' );
	}

    public static function Redirect( $url, $delay = 0 ) {
        if ( !$delay ) {
            header( "Location:{$url}" );
            die();
            
        } else {
            header( "Refresh: {$delay}; url={$url}" );
        }
    
    }
    
}