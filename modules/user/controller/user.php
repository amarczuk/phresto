<?php

namespace Phresto\Modules\Controller;
use Phresto\CustomModelController;
use Phresto\View;

/** 
* Additional user's REST endpoints
*/
class user extends CustomModelController {

	const CLASSNAME = __CLASS__;
	const MODELCLASS = 'Phresto\\Modules\\Model\\user';

	protected function authenticate_post( $email, $password ) {
		return View::jsonResponse( ['email' => $email, 'pass' => $password] );
	}

}