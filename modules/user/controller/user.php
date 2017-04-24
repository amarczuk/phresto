<?php

namespace Phresto\Modules\Controller;
use Phresto\CustomModelController;
use Phresto\View;
use Phresto\Config;
use Phresto\Modules\GoogleApi;
use Phresto\Modules\FBApi;
use Phresto\Modules\GithubApi;
use Phresto\Modules\LinkedinApi;
use Phresto\Exception\RequestException;

/** 
* Additional user's REST endpoints
*/
class user extends CustomModelController {

	const CLASSNAME = __CLASS__;
	const MODELCLASS = 'Phresto\\Modules\\Model\\user';

	public function authenticate_post( string $email, string $password ) {
		$token = User::login( $email, $password );

		return View::jsonResponse( [ 'token' => $token->token, 'expires' => $token->expires ] );
	}

	/** 
	* login/register using google's credentials
	*/
	public function google_get() {
		try {
			$conf = $Config->getConfig( 'social' );
			$oauth = new GoogleApi( $conf['google']['key'], $conf['google']['secret'] );
			
			$token = User::socialLogin( $oauth->getUserDetails() );
			$view = View::getView( 'oauth' );
			$view->add( 'oauthSuccess', [ 'token' => $token->token, 'expires' => $token->expires ] );
			return $view->get();
			
		} catch( \Exception $e ) {
			throw new RequestException( 403 );
		}
	}

	/** 
	* login/register using facebook's credentials
	*/
	public function facebook_get() {
		try {
			$conf = Config::getConfig( 'social' );
			$oauth = new FBApi( $conf['fb']['key'], $conf['fb']['secret'] );
			
			$token = User::socialLogin( $oauth->getUserDetails() );
			$view = View::getView( 'oauth' );
			$view->add( 'oauthSuccess', [ 'token' => $token->token, 'expires' => $token->expires ] );
			return $view->get();
			
		} catch( \Exception $e ) {
			throw new RequestException( 403 );
		}
	}

	/** 
	* login/register using github's credentials
	*/
	public function github_get() {
		try {
			$conf = Config::getConfig( 'social' );
			$oauth = new GithubApi( $conf['github']['key'], $conf['github']['secret'] );
			
			$token = User::socialLogin( $oauth->getUserDetails() );
			$view = View::getView( 'oauth' );
			$view->add( 'oauthSuccess', [ 'token' => $token->token, 'expires' => $token->expires ] );
			return $view->get();
			
		} catch( \Exception $e ) {
			throw new RequestException( 403 );
		}
	}

	/** 
	* login/register using linkedin's credentials
	*/
	public function linkedin_get() {
		try {
			$conf = Config::getConfig( 'social' );
			$oauth = new LinkedinApi( $conf['linkedin']['key'], $conf['linkedin']['secret'] );
			
			$token = User::socialLogin( $oauth->getUserDetails() );
			$view = View::getView( 'oauth' );
			$view->add( 'oauthSuccess', [ 'token' => $token->token, 'expires' => $token->expires ] );
			return $view->get();
			
		} catch( \Exception $e ) {
			throw new RequestException( 403 );
		}
	}

}