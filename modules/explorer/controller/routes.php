<?php

namespace Phresto\Modules\Controller;
use Phresto\Controller;
use Phresto\ModelController;
use Phresto\Config;
use Phresto\View;

class routes extends Controller {

	const CLASSNAME = __CLASS__;

	public function get() {
		$modules = Config::getConfig( 'modules' );
		$endpoints = [];
		$controllers = [];

		foreach ( $modules as $modname => $module ) {
			if ( is_array( $module['Controller'] ) ) {
				foreach ( $module['Controller'] as $file ) {
					$name = str_replace( '.php', '', $file );
					if ( in_array( $name, $endpoints ) ) continue;
					$class = '\\Phresto\\Modules\\Controller\\' . $name;
					$discovery = $class::discover();
					$controllers = array_merge( $controllers, $discovery );
				}
			}

			if ( is_array( $module['Model'] ) ) {
				foreach ( $module['Model'] as $file ) {
					$name = str_replace( '.php', '', $file );
					if ( in_array( $name, $endpoints ) ) continue;
					$class = '\\Phresto\\Modules\\Model\\' . $name;
					$controllers = array_merge( $controllers, ModelController::discover( $class ) );
				}
			}
		}
		return View::jsonResponse( $controllers );
	}
}