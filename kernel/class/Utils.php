<?php

namespace Phresto;

require_once( __DIR__ . '/Config.php' );

class Utils {

	public static function autoload( $className ) {
		$base = __DIR__ . '/../../';
		$path = explode( '\\', trim( $className, '\\' ) );
		$modules = Config::getConfig( 'modules' );

		if ( empty( $modules ) ) {
			self::updateModules();
			$modules = Config::getConfig( 'modules' );
		}

		if ( $path[0] != 'Phresto' ) return;
		if ( in_array( $path[1], [ 'Interf', 'Exception' ] ) ) {
			$file = $base . 'kernel/' . mb_strtolower( $path[1] ) . '/' . $path[2] . '.php';
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
		$app = Config::getConfig( 'app' );
		if ( $app['app']['env'] == 'dev' ) {
			Config::delConfig( 'modules' );
		}
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

    public static function updateModules() {
    	$getFiles = function( $base, $flag = 0 ) {
			return array_map( function( $elem ) use ( $base, $flag ) { return str_replace( $base, '', $elem ); }, glob( $base . '*', $flag ) );
		};

		$base = __DIR__ . '/../../modules/';
		$modules = $getFiles( $base, GLOB_ONLYDIR );

		$types = ['Controller', 'Model', 'class', 'Interf'];

		$config = [];

		foreach ( $modules as $module ) {
			foreach ( $types as $type ) {
				$files = $getFiles( $base . $module . '/' . $type . '/' );
				$config[$module][$type] = $files;
			}
		}


		Config::saveConfig( 'modules', $config );
    }
    
}