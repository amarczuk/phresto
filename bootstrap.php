<?php

require_once 'kernel/class/Utils.php';

Phresto\Utils::registerAutoload();

try {
	echo Phresto\Router::route();
} catch( Exception $e ) {
	echo Phresto\Router::routeException( $e->getMessage() );
}
