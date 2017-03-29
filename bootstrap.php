<?php

require_once 'kernel/class/Utils.php';

Phresto\Utils::registerAutoload();

try {
	$response = Phresto\Router::route();
	header( 'Content-Type: ' . $response['content-type'] );
	echo $response['body'];
} catch( Phresto\Exception\RequestException $e ) {
	echo Phresto\Router::routeException( $e->getMessage() );
} catch( Exception $e ) {
	echo Phresto\Router::routeException( 500, $e->getMessage(), $e->getTrace() );
}
