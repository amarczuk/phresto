<?php


use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\Facebook;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;;

class FBApi {

    // Session storage
    protected $storage;
    protected $credentials;
    protected $token;
    protected $fbService;

    public function __construct( $key, $secret ) {
        $url = $_SERVER["HTTP_HOST"] . '/facebook';
        if ( $_SERVER["HTTPS"] == 'on' ) {
            $url = 'https://' . $url;
        } else {
            $url = 'http://' . $url;
        }
        $this->storage = new Session();
        $this->credentials = new Credentials(
            $key,
            $secret,
            $url
        );

        $serviceFactory = new ServiceFactory();
        $this->fbService = $serviceFactory->createService( 'facebook', $this->credentials, $this->storage, [] );
    }

    public function getUserDetails() {

        if ( !empty( $_GET['error'] ) ) {
            throw new Exception( $_GET['error'] );
        }

        if ( !empty($_GET['code']) ) {
            // retrieve the CSRF state parameter
            $state = isset( $_GET['state'] ) ? $_GET['state'] : null;
            $this->fbService->requestAccessToken( $_GET['code'], $state );
            $user = [];
            $result1 = json_decode( $this->fbService->request('/me'), true );
            try {
                $result2 = $this->fbService->request( '/me/picture?type=large' );
            } catch( Exception $e ) {
                $result2 = null;
            }
            $user['email'] = $result1['email'];
            $user['given_name'] = $result1['first_name'];
            $user['name'] = $result1['name'];
            $user['raw_picture'] = $result2;
            return $user;

        } else {
            if ( !empty( $_GET['p'] ) ) {
                $_SESSION['login_from'] = $_GET['p'];
            } else {
                $_SESSION['login_from'] = '';
            }
            Misc::Load( $this->fbService->getAuthorizationUri() );
        }
    }
}