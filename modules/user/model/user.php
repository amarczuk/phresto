<?php

namespace Phresto\Modules\Model;
use Phresto\MySQLModel;

class user extends MySQLModel {
	const CLASSNAME = __CLASS__;

    const DB = 'mysql';
    const NAME = 'user';
    const INDEX = 'id';
    const COLLECTION = 'user';

    protected static $_fields = [ 'id' => 'int', 
                                  'email' => 'string', 
                                  'email_md5' => 'string', 
                                  'password' => 'string', 
                                  'name' => 'string', 
                                  'status' => 'int', 
                                  'created' => 'DateTime', 
                                  'last_login' => 'DateTime' 
                                ];

    protected static $_defaults = [ 'status' => 1, 'created' => '' ];
    protected static $_relations = [
        'token' => [
            'type' => '1:n',
            'model' => 'token',
            'field' => 'user',
            'index' => 'id'
        ]
    ];

    protected function image_value() {
        return '//www.gravatar.com/avatar/' . $this->email_md5 . '?d=retro';
    }

    protected function saveFilter() {
        $this->email_md5 = md5( $this->email );
        if ( $this->_initial['password'] != $this->password ) {
            $this->password = md5( md5( $this->password ) );
        }
    }

    protected function default_created() {
        return new \DateTime();
    }

    protected function filterJson( $fields ) {
    	$fields['password'] = '* * *';
        $fields['image'] = $this->image;
    	return $fields;
    }
}