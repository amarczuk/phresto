<?php


use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\Google;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

class GoogleApi {

    // Session storage
    protected $storage;
    protected $credentials;
    protected $token;
    protected $googleService;

    public function __construct( $key, $secret ) {
        $url = $_SERVER["HTTP_HOST"] . '/google';
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
        $this->googleService = $serviceFactory->createService( 'google', $this->credentials, $this->storage, [ 'userinfo_email', 'userinfo_profile' ] );
    }

    public function getUserDetails() {

        if ( !empty( $_GET['error'] ) ) {
            throw new Exception( $_GET['error'] );
        }

        if ( !empty($_GET['code']) ) {
            // retrieve the CSRF state parameter
            $state = isset( $_GET['state'] ) ? $_GET['state'] : null;
            $this->googleService->requestAccessToken( $_GET['code'], $state );
            $result = json_decode( $this->googleService->request( 'userinfo' ), true );
            return $result;

        } else {
            if ( !empty( $_GET['p'] ) ) {
                $_SESSION['login_from'] = $_GET['p'];
            } else {
                $_SESSION['login_from'] = '';
            }
            Misc::Load( $this->googleService->getAuthorizationUri() );
        }
    }
}