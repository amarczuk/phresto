<?php

namespace Phresto\Modules\Controller;
use Phresto\Controller;
use Phresto\View;

class login extends Controller {

	const CLASSNAME = __CLASS__;

	public function get() {
		$view = View::getView( 'main', 'main' );
		$view->add( 'login', [], 'main' );

		return $view->get();
	}

	public function post($user, $password) {
		$view = View::getView( 'main', 'main' );
		$view->add( 'afterlogin', ['user'=>$user, 'password'=>$password], 'main' );

		return $view->get();
	}
}