<?php

namespace Phresto;

use Phresto\View;

class Router {

	public static function route() {
		$reqType = mb_strtolower( $_SERVER['REQUEST_METHOD'] );
        $route = explode( '/', trim( $_GET['PHRESTOREQUESTPATH'], '/' ) );
        $class = array_shift( $route );
        $query = $_GET;
        unset( $query['PHRESTOREQUESTPATH'] );
        $bodyRaw = '';
        $body = [];
        $headers = [];

		if ( $reqType != 'get' && $reqType != 'delete' ) {
	        $bodyRaw = @file_get_contents('php://input');

	        if ( mb_strpos( $_SERVER["CONTENT_TYPE"], 'application/json' ) !== false ) {
	        	$body = json_decode( $bodyRaw, true );
	        }

	        if ( empty( $body ) ) {
	        	$body = [];
	        	parse_str( $bodyRaw, $body );
	        }

	        if ( empty( $body ) && !empty( $_POST ) ) {
	        	$body = $_POST;
	        	$bodyRaw = http_build_query( $_POST );
	        }
	    }

		if ( empty( $class ) ) {
        	if ( class_exists( 'Phresto\\Modules\\Controller\\Main' ) ) {
        		$instance = Container::{'Phresto\\Modules\\Controller\\Main'}( $reqType, $route, $body, $bodyRaw, $query, $headers );
        	} else if ( file_exists( 'static/index.html' ) ) {
        		return file_get_contents( 'static/index.html' );
        	} else {
        		throw new Exception\RequestException( '404' );
        	}
        } else {
		    if ( class_exists( 'Phresto\\Modules\\Controller\\' . $class ) ) {
		    	$instance = Container::{'Phresto\\Modules\\Controller\\' . $class}( $reqType, $route, $body, $bodyRaw, $query, $headers );
		    } else if ( class_exists( 'Phresto\\Modules\\Model\\' . $class ) ) {
		    	$instance = Container::ModelController( 'Phresto\\Modules\\Model\\' . $class, $reqType, $route, $body, $bodyRaw, $query, $headers );
		    } else {
		    	throw new Exception\RequestException( '404' );
		    }
		}

	    return $instance->exec();
	}

	public static function routeException( $ex = 500, $message = '', $trace = '' ) {
		http_response_code((int)$ex);
		$resp = [
			'status' => $ex,
			'message' => $message,
			'trace' => $trace
		];
		if ( mb_strpos( $_SERVER["CONTENT_TYPE"], 'application/json' ) !== false ) {
			return View::jsonResponse( $resp );
		}

		$view = View::getView('error');
		$view->add('error', $resp);
		
		return $view->get();
	}
}
