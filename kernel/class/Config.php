<?php

namespace Phresto;

class Config {

	public static $CurrentModule;
	private static $configCache = [];
    
    public static function getConfig( $name, $module = null ) {
        $path = self::getPath( $name, $module );
        if ( isset( self::$configCache[ $path ] ) ) {
    		return self::$configCache[ $path ];
    	}

        if ( !is_file( $path ) && !empty( $module ) ) {
            $path = self::getPath( $name );
        }

        if ( !is_file( $path ) ) return [];

        self::$configCache[ $path ] = parse_ini_file( $path, true );
        return self::$configCache[ $path ];
    }

    public static function mockConfig( $name, $config, $module = null ) {
        $path = self::getPath( $name, $module );
    	self::$configCache[$path] = $config;
    }

    public static function clearCache() {
    	self::$configCache = [];
    }

    private static function getPath( $name, $module = null ) {
        $base = __DIR__ . '/../..';
        return ( empty( $module ) ) ? "{$base}/kernel/config/{$name}.ini" : "{$base}/modules/{$module}/config/{$name}.ini";
    }

    public static function saveConfig( $name, $config, $module = null ) {
        $content = "";

        foreach ( $config as $key => $elem ) {
            $content .= "[".$key."]\n";
            foreach ( $elem as $key2 => $elem2 ) {
                if( is_array( $elem2  ) ) {
                    for( $i = 0; $i < count( $elem2 ); $i++ ) {
                        $content .= "{$key2}[] = " . self::getElementText( $elem2[$i] );
                    }
                } else {
                    $content .= "{$key2} = " . self::getElementText( $elem2 );
                }
            }
            $content .= "\n";
        }

        $path = self::getPath( $name, $module );
        $success = file_put_contents( $path, $content );
        unset( self::$configCache[$path] );

        return $success;
    }
	
    private static function getElementText( $value ) {
        if ( $value == "" ) {
            return "\n";
        } else if ( is_string( $value ) ) {
            return "\"{$value}\"\n";
        } else if ( is_bool( $value ) ) {
            return ( $value ) ? "true\n" : "false\n";
        }
            
        return "{$value}\n";
    }

}
