<?php

namespace Phresto;

use Phresto\View;

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

	protected function getRouteMapping( $reqType ) {
		if ( is_array( $this->routeMapping[$reqType] ) ) {
			return $this->routeMapping[$reqType];
		}

		if ( is_array( $this->routeMapping['all'] ) ) {
			return $this->routeMapping['all'];
		}

		return [];
	}

	protected function getMethod() {
		$reflection = new \ReflectionClass( static::CLASSNAME );

		if ( !empty($this->route[0]) && $reflection->hasMethod( $this->route[0] . '_' . $this->reqType ) ) {
			$method = $reflection->getMethod( $this->route[0] . '_' . $this->reqType );
			array_shift( $this->route );
		} else if ( $reflection->hasMethod( $this->reqType ) ) {
			$method = $reflection->getMethod( $this->reqType );
		} else {
			throw new Exception\RequestException( '404' );
		}

		$params = $method->getParameters();
		$args = [];
		$routeMapping = $this->getRouteMapping( $method->name );
		foreach ( $params as $param ) {
			if ( !empty( $routeMapping ) && isset( $routeMapping[$param->name] ) && isset( $this->route[$routeMapping[$param->name]] ) && $this->route[$routeMapping[$param->name]] != '') {
				$args[] = $this->getParamValue( $param, $this->route[$routeMapping[$param->name]] );
			} else if ( isset( $this->body[$param->name] ) ) {
				$args[] = $this->getParamValue( $param, $this->body[$param->name] );
			} else if ( isset( $this->query[$param->name] ) ) {
				$args[] = $this->getParamValue( $param, $this->query[$param->name] );
			} else  if ( $param->isDefaultValueAvailable() ) {
				$args[] = $param->getDefaultValue();
			} else {
				$args[] = null;
			}
		}

		return [ $method, $args ];
	}

	public function exec() {
		list( $method, $args ) = $this->getMethod();
		$method->setAccessible( true );
		return $method->invokeArgs( $this, $args );
	}

	protected function getParamValue(\ReflectionParameter $param, $value) {
		if ( defined( PHP_MAJOR_VERSION ) && PHP_MAJOR_VERSION >= 7) {
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

	/**
	* prints controller description
	* @return object
	*/
	protected function discover_get() {
		return View::jsonResponse( static::discover() );
	}

	protected static function getParameters( $method, $className ) {
		return $method->getParameters();
	}

	public static function discover( $className = null ) {

		$hasParam = function( $params, $field ) {
			foreach ($params as $param) {
				$name = ( is_object($param) ) ? $param->name : $param;
				if ($name == $field) return true;
			}

			return false;
		};

		$getDescription = function( $desc ) {
			return trim( preg_replace ( ['$^[\s]*/\*\*$isU', '$[\s]*\*\/$isU', '$[\s]*\*[\s]*$isU'], ['', '', "\n"], $desc ) );
		};

		$reflection = new \ReflectionClass( static::CLASSNAME );

		$requestTypes = [ 'get', 'post', 'patch', 'put', 'delete', 'head' ];
		$endpoints = [];
		
		$tmp = explode( '\\', ( isset( $className ) ) ? $className : static::CLASSNAME );
		$classNameOnly = array_pop( $tmp );

		$classMethods = $reflection->getMethods( \ReflectionMethod::IS_PUBLIC + \ReflectionMethod::IS_PROTECTED );
		$staticProps = $reflection->getDefaultProperties(); 
		$fields = $staticProps['routeMapping'];

		foreach ( $classMethods as $method ) {
			if ( !in_array( $method->name, $requestTypes ) && 
				 !( strpos( $method->name, '_' ) !== false && 
				 	in_array( substr( $method->name, strpos( $method->name, '_' ) + 1 ), $requestTypes )
				  )
				) continue;

			$describe = [ 'name' => $method->name, 'urlparams' => [], 'params' => [] ];
			$params = static::getParameters( $method, $className );
			$ignore = [];

			$routeMapping = ( is_array( $fields[$method->name] ) ) ? $fields[$method->name] : ( is_array( $fields['all'] ) ) ? $fields['all'] : [];
			
			if ( !empty( $routeMapping ) ) {
				$values = array_values($routeMapping);
				if ( is_array($values[0] ) ) $routeMapping = [];
				asort( $routeMapping );
				foreach ( $routeMapping as $field => $index ) {
					if ( $hasParam($params, $field) ) {
						$describe['urlparams'][$index] = $field;
						$ignore[] = $field;
					}
				}
			}

			$describe['urlparams'] = array_values( $describe['urlparams'] );

			foreach ( $params as $param ) {
				$name = ( is_object($param) ) ? $param->name : $param;
				if ( in_array($name, $ignore ) ) continue;
				$describe['params'][] = $name;
			}

			$methodName = $method->name;
			if ( strpos( $method->name, '_' ) !== false ) {
				list($methodName, $reqType) = explode('_', $methodName);
			}

			$endpoint = $classNameOnly;
			if ( isset( $reqType ) ) {
				$endpoint .= '/' . $methodName;
				$methodName = $reqType;
			}

			if ( !is_array( $endpoints[$endpoint] ) ) {
				$endpoints[$endpoint] = ['endpoint' => $endpoint, 'methods' => [], 'description' => ''];
				if ( !isset( $reqType ) ) {
					$endpoints[$endpoint]['description'] = $getDescription( $reflection->getDocComment() );
				}
			}

			$describe['description'] = $getDescription( $method->getDocComment() );
			$describe['name'] = $methodName;
			unset($reqType);
			unset($methodName);

			$endpoints[$endpoint]['methods'][] = $describe;
		}

		return array_values( $endpoints );
	}

}