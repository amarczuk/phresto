<?php

require_once __DIR__ . '/../kernel/class/Utils.php';

Phresto\Utils::registerAutoload();

function getFiles( $base, $flag = 0 ) {
	return array_map( function( $elem ) use ( $base, $flag ) { return str_replace( $base, '', $elem ); }, glob( $base . '*', $flag ) );
}

$base = __DIR__ . '/../modules/';
$modules = getFiles( $base, GLOB_ONLYDIR );

$types = ['Controller', 'Model', 'class', 'Interf'];

$config = [];

foreach ( $modules as $module ) {
	foreach ( $types as $type ) {
		$files = getFiles( $base . $module . '/' . $type . '/' );
		$config[$module][$type] = $files;
	}
}


Phresto\Config::saveConfig( 'modules', $config );