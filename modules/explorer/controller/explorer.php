<?php

namespace Phresto\Modules\Controller;
use Phresto\Controller;
use Phresto\View;

class explorer extends Controller {

	const CLASSNAME = __CLASS__;

	public function get() {
		$view = View::getView( 'main', 'explorer' );
		$view->add( 'main', [], 'explorer' );

		return $view->get();
	}

	public function post( $a, $b, $c, $d ) {

	}
}