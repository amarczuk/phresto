<?php

namespace Phresto\Modules\Model;
use Phresto\MySQLModel;

class token extends MySQLModel {
	const CLASSNAME = __CLASS__;

    const DB = 'mysql';
    const NAME = 'token';
    const INDEX = 'id';
    const COLLECTION = 'token';

    protected static $_fields = [ 'id', 'created', 'token', 'user', 'ttl' ];
    protected static $_defaults = [ 'ttl' => 7, 'created' => '' ];
    protected static $_relations = [
        'user' => [
            'type' => 'n:1',
            'model' => 'user',
            'field' => 'id',
            'index' => 'user'
        ]
    ];
    
    protected function default_created() {
        return date( 'Y-m-d H:i' );
    }

    protected function saveFilter() {
        if ( $this->_new ) $this->token = uniqid();
    }

    protected function filterJson( $fields ) {
    	$fields['token'] = '*********';
    	return $fields;
    }
}