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
				$args[] = $this->body[$param->name];
			} else if ( isset( $this->query[$param->name] ) ) {
				$args[] = $this->query[$param->name];
			} else if ( isset( $this->routeMapping[$param->name] ) && isset( $this->route[$this->routeMapping[$param->name]] ) ) {
				$args[] = $this->route[$this->routeMapping[$param->name]];
			} else {
				$args[] = $param->getDefaultValue();
			}
		}

		$method->setAccessible( true );
		return $method->invokeArgs( $this, $args );
	}

	protected function auth() {
		return true;
	}

}