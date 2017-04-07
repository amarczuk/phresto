<?php

namespace Phresto\Modules\Model;
use Phresto\MySQLModel;

class user extends MySQLModel {
	const CLASSNAME = __CLASS__;

    const DB = 'mysql';
    const NAME = 'user';
    const INDEX = 'id';
    const COLLECTION = 'user';

    protected static $_fields = [ 'id', 'email', 'pass', 'name', 'nick', 'date_payment', 'status', 'date_added', 'date_logged' ];
    protected static $_relations = [
        'token' => [
            'type' => '1:n',
            'model' => 'token',
            'field' => 'user',
            'index' => 'id'
        ]
    ];

    protected function filterJson( $fields ) {
    	$fields['pass'] = '*********';
    	return $fields;
    }
}