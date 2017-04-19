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

	protected function authenticate_post( $email, $password, int $nr = 0, \DateTime $date = null ) {
		if ( empty($date) ) $date = new \DateTime();
		return View::jsonResponse( ['email' => $email, 'pass' => $password, 'nr' => $nr, 'date' => $date->format( \DateTime::ISO8601 )] );
	}

}