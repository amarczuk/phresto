<?php


use OAuth\ServiceFactory;
use OAuth\OAuth2\Service\Linkedin;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;;

class LinkedinApi {

    // Session storage
    protected $storage;
    protected $credentials;
    protected $token;
    protected $aService;

    public function __construct( $key, $secret ) {
        $url = $_SERVER["HTTP_HOST"] . '/linkedin';
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
        $this->aService = $serviceFactory->createService( 'linkedin', $this->credentials, $this->storage, [ 'r_basicprofile', 'r_emailaddress' ] );
    }

    public function getUserDetails() {

        if ( !empty( $_GET['error'] ) ) {
            throw new Exception( $_GET['error'] );
        }

        if ( !empty($_GET['code']) ) {
            // retrieve the CSRF state parameter
            $state = isset( $_GET['state'] ) ? $_GET['state'] : null;
            $this->aService->requestAccessToken( $_GET['code'], $state );
            $user = [];
            $result = json_decode( $this->aService->request( '/people/~:(first-name,last-name,picture-urls::(original),email-address)?format=json' ), true );
            $user['email'] = $result['emailAddress'];
            $user['given_name'] = $result['firstName'];
            $user['name'] = $result['firstName'] . ' ' . $result['lastName'];
            if (  $result['pictureUrls']['_total'] > 0 ) {
                $user['picture'] = $result['pictureUrls']['values'][0];
            }
            return $user;

        } else {
            if ( !empty( $_GET['p'] ) ) {
                $_SESSION['login_from'] = $_GET['p'];
            } else {
                $_SESSION['login_from'] = '';
            }
            Misc::Load( $this->aService->getAuthorizationUri() );
        }
    }
}