<?php

namespace Phresto\Modules\Controller;
use Phresto\Controller;
use Phresto\Config;

class routes extends Controller {

	const CLASSNAME = __CLASS__;

	public function get() {
		$modules = Config::getConfig( 'modules' );
		$models = [];
		$controllers = [];

		foreach ( $modules as $modname => $module ) {
			if ( !empty( $module['Controller'] ) ) {
				foreach ( $module['Controller'] as $file ) {
					$name = str_replace( '.php', '', $file );
					array_push($controllers, [ 'module' => $modname, 'endpoint' => $name, 'methods' => $this->getMethods( 'controller', 'Phresto\\Modules\\Controller\\' . $name ) ]);
				}
			}

			if ( !empty( $module['Model'] ) ) {
				foreach ( $module['Model'] as $file ) {
					$name = str_replace( '.php', '', $file );
					array_push($models, [ 'module' => $modname, 'endpoint' => $name, 'methods' => $this->getMethods( 'model', 'Phresto\\Modules\\Model\\' . $name ) ]);
				}
			}
		}
		return json_encode( ['controllers' => $controllers, 'models' => $models ],  JSON_PRETTY_PRINT );
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

					$methods[] = [ 'name' => $request, 'urlparams' => $params, 'bodyparams' => $bodyparams ];
				}
			}

			return $methods;
		}
		
		$classMethods = $reflection->getMethods( \ReflectionMethod::IS_PUBLIC );

		foreach ( $classMethods as $method ) {

			if ( !array_key_exists( $method->name, $requestTypes ) ) continue;

			$describe = [ 'name' => $method->name, 'urlparams' => [] ];
			$params = $method->getParameters();
			foreach ( $params as $param ) {
				$describe['params'][] = $param->name;
			}

			$methods[] = $describe;
		}

		return $methods;
	}
}