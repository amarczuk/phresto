<?php

namespace Phresto\Modules\Controller;
use Phresto\Controller;
use Phresto\ModelController;
use Phresto\Config;
use Phresto\View;

/** 
* Tool to make testing your application easier
*/
class explorer extends Controller {

	const CLASSNAME = __CLASS__;
	protected $routeMapping = [ 'post' => [ 'a' => 0, 'b' => 2, 'd' => 1 ] ];

	/** 
	* returns explorer's UI
	* @return html
	*/
	public function get() {
		$view = View::getView( 'main', 'explorer' );
		$view->add( 'main', [], 'explorer' );

		return $view->get();
	}

	/** 
	* returns all routes available in the app
	* @return json
	*/
	public function routes_get() {
		$modules = Config::getConfig( 'modules' );
		$endpoints = [];
		$controllers = [];

		foreach ( $modules as $modname => $module ) {
			if ( is_array( $module['Controller'] ) ) {
				foreach ( $module['Controller'] as $file ) {
					$name = str_replace( '.php', '', $file );
					if ( in_array( $name, $endpoints ) ) continue;

					$class = '\\Phresto\\Modules\\Controller\\' . $name;
					if ( !class_exists( $class ) ) continue;

					$controllers = array_merge( $controllers, $class::discover() );
				}
			}

			if ( is_array( $module['Model'] ) ) {
				foreach ( $module['Model'] as $file ) {
					$name = str_replace( '.php', '', $file );
					if ( in_array( $name, $endpoints ) ) continue;

					$class = '\\Phresto\\Modules\\Model\\' . $name;
					if ( !class_exists( $class ) ) continue;

					$controllers = array_merge( $controllers, ModelController::discover( $class ) );
				}
			}
		}
		
		return View::jsonResponse( $controllers );
	}
}