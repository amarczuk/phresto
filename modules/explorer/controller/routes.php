<?php

namespace Phresto\Modules\Controller;
use Phresto\Controller;
use Phresto\Config;
use Phresto\View;

class routes extends Controller {

	const CLASSNAME = __CLASS__;

	public function get() {
		$modules = Config::getConfig( 'modules' );
		$endpoints = [];
		$controllers = [];

		foreach ( $modules as $modname => $module ) {
			if ( !empty( $module['Controller'] ) ) {
				foreach ( $module['Controller'] as $file ) {
					$name = str_replace( '.php', '', $file );
					if ( in_array( $name, $endpoints ) ) continue;
					$endpoints[] = $name;
					array_push($controllers, [ 'module' => $modname, 'endpoint' => $name, 'methods' => $this->getMethods( 'controller', 'Phresto\\Modules\\Controller\\' . $name ) ]);
				}
			}

			if ( !empty( $module['Model'] ) ) {
				foreach ( $module['Model'] as $file ) {
					$name = str_replace( '.php', '', $file );
					if ( in_array( $name, $endpoints ) ) continue;
					$endpoints[] = $name;
					array_push($controllers, [ 'module' => $modname, 'endpoint' => $name, 'methods' => $this->getMethods( 'model', 'Phresto\\Modules\\Model\\' . $name ) ]);
				}
			}
		}
		return View::jsonResponse( [ 'controllers' => $controllers ] );
	}

	private function hasParam( $params, $field ) {
		foreach ($params as $param) {
			if ($param->name == $field) return true;
		}

		return false;
	}

	private function getMethods( $type, $class ) {
		$reflection = new \ReflectionClass( $class );

		$requestTypes = [ 'get' => ['id'], 'post' => [], 'patch' => ['id'], 'put' => ['id'], 'delete' => ['id'], 'head' => ['id'] ];
		$withBody = [ 'post', 'patch', 'put' ];
		$methods = [];

			
		if ( $type == 'model' ) {
			$staticProps = $reflection->getStaticProperties(); 
			$fields = $staticProps['_fields'];
			foreach ( $requestTypes as $request => $params ) {
				if ( !array_key_exists( $request, $methods ) ) {
					$bodyparams = ( in_array( $request, $withBody ) ) ? $fields : [];

					$methods[] = [ 'name' => $request, 'urlparams' => $params, 'params' => $bodyparams ];
				}
			}

			return $methods;
		}
		
		$classMethods = $reflection->getMethods( \ReflectionMethod::IS_PUBLIC );
		$staticProps = $reflection->getDefaultProperties(); 
		$fields = $staticProps['routeMapping'];

		foreach ( $classMethods as $method ) {

			if ( !array_key_exists( $method->name, $requestTypes ) ) continue;

			$describe = [ 'name' => $method->name, 'urlparams' => [] ];
			$params = $method->getParameters();
			$ignore = [];

			$routeMapping = ( is_array($fields[$method->name] ) ) ? $fields[$method->name] : $fields;
			$values = array_values($routeMapping);
			if ( is_array($values[0] ) ) $routeMapping = [];

			foreach ( $routeMapping as $field => $index ) {
				if ( $this->hasParam($params, $field) ) {
					$describe['urlparams'][$index] = $field;
					$ignore[] = $field;
				}
			}

			foreach ( $params as $param ) {
				if ( in_array($param->name, $ignore ) ) continue;
				$describe['params'][] = $param->name;
			}

			$methods[] = $describe;
		}

		return $methods;
	}
}