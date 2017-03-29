<?php

namespace Phresto;

class Controller {

	const CLASSNAME = __CLASS__;

	protected $routeMapping = [];
	protected $queryDescription = [];

	protected $headers = [];
	protected $body = [];
	protected $bodyRaw = '';
	protected $query = [];
	protected $route = [];
	protected $reqType = 'get';

	public function __construct( $reqType, $route, $body, $bodyRaw, $query, $headers ) {
		$this->reqType = $reqType;
		$this->route = $route;
		$this->headers = $headers;
		$this->body = $body;
		$this->query = $query;
		$this->bodyRaw = $bodyRaw;

		if ( !$this->auth() ) {
			throw new Exception\RequestException( '403' );
		}
	}

	public function exec() {
		$reflection = new \ReflectionClass( static::CLASSNAME );

		if ( $reflection->hasMethod( $this->reqType ) ) {
			$method = $reflection->getMethod( $this->reqType );
		} else {
			throw new Exception\RequestException( '404' );
		}

		$params = $method->getParameters();
		$args = [];
		foreach ( $params as $param ) {
			if ( isset( $this->body[$param->name] ) ) {
				$args[] = $this->getParamValue( $param, $this->body[$param->name] );
			} else if ( isset( $this->query[$param->name] ) ) {
				$args[] = $this->getParamValue( $param, $this->query[$param->name] );
			} else if ( isset( $this->routeMapping[$param->name] ) && isset( $this->route[$this->routeMapping[$param->name]] ) && $this->route[$this->routeMapping[$param->name]] != '') {
				$args[] = $this->getParamValue( $param, $this->route[$this->routeMapping[$param->name]] );
			} else if ( isset( $this->routeMapping[$this->reqType] ) && isset( $this->routeMapping[$this->reqType][$param->name] ) && isset( $this->route[$this->routeMapping[$this->reqType][$param->name]] ) && $this->route[$this->routeMapping[$this->reqType][$param->name]] != '') {
				$args[] = $this->getParamValue( $param, $this->route[$this->routeMapping[$this->reqType][$param->name]] );
			} else if ( $param->isDefaultValueAvailable() ) {
				$args[] = $param->getDefaultValue();
			} else {
				$args[] = null;
			}
		}

		$method->setAccessible( true );
		return $method->invokeArgs( $this, $args );
	}

	protected function getParamValue(\ReflectionParameter $param, $value) {
		if ( defined( PHP_MAJOR_VERSION ) && PHP_MAJOR_VERSION >= 7)
			if ( $param->hasType() ) {
				$type = $param->getType();
				if ( $type == 'boolean' && $value == 'false' ) {
					$value = false;
				} else {
					settype( $value, $param->getType() );
				}
			}
		}
		// TODO: maybe use doc comment to get types in PHP5?
		return $value;
	}

	protected function auth() {
		return true;
	}

}