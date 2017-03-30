<?php

require_once 'kernel/class/Utils.php';

Phresto\Utils::registerAutoload();

try {
	$response = Phresto\Router::route();
} catch( Phresto\Exception\RequestException $e ) {
	$response = Phresto\Router::routeException( $e->getMessage() );
} catch( Exception $e ) {
	$response = Phresto\Router::routeException( 500, $e->getMessage(), $e->getTrace() );
}

header( 'Content-Type: ' . $response['content-type'] );
echo $response['body'];