<?php

require_once 'kernel/class/Utils.php';

Phresto\Utils::registerAutoload();

try {
	$response = Phresto\Router::route();
	header( 'Content-Type: ' . $response['content-type'] );
	echo $response['body'];
} catch( Exception $e ) {
	echo Phresto\Router::routeException( $e->getMessage() );
}
