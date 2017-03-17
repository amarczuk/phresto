<?php

namespace Phresto\Modules\Controller;
use Phresto\Controller;
use Phresto\View;

class Main extends Controller {

	const CLASSNAME = __CLASS__;

	public function get($user) {
		$view = View::getView( 'main', 'main' );
		$view->add( 'hello', [ 'name' => $user, 'colors' => [ ['name'=>'green'], ['name'=>'yellow']] ], 'main' );

		return $view->get();
	}
}