<?php

namespace Phresto;
use Phresto\Interf\ModelInterface;

class Model implements ModelInterface, \JsonSerializable {

    const CLASSNAME = __CLASS__;

    const DB = 'maindb';
	const NAME = 'model';
	const INDEX = 'id';
    const COLLECTION = 'model';
    
    protected $_properties = [];
	protected static $_fields = [];
    protected static $_relations = [];
    protected $_new = true;

    public function __construct( $option = null ) {
        
        if ( empty( $option ) ) {
            $this->getEmpty();
            return;
        }
        
        if ( is_array( $option ) && isset( $option['where'] ) ) {
            $option['limit'] = 1;
            $this->set( self::find( $option )[0] );
            return;
        }
        
        if ( is_array( $option ) ) {
            $this->set( $modelArray );
            return;
        }

        if ( is_object( $option ) ) {
            $this->setObject( $model );
            return;
        }

        if ( is_string( $option ) && !is_numeric( $option) && $json = json_decode( $option, true ) ) {
            $this->set( $json );
            return;
        }

        $this->getById( $option );
    }

    public static function auth( $reqType ) {
        return true;
    }

    public static function getIndexField() {
        return static::INDEX;
    }

    public function setIndex( $id ) {
        $this->_properties[static::INDEX] = $id;
        $this->_new = false;
    }

    public static function getFields() {
        return static::$_fields;
    }

    protected function getEmpty() {
    	$this->_properties = [];
    	foreach( static::$_fields as $field ) {
    		$this->_properties[$field] = '';
    	}
    	$this->_new = true;
    }

    protected function getById( $id ) {
        $this->set( static::find( [ 'where' => [ static::INDEX => $id ], 'limit' => 1 ] )[0] );
    }

    protected function set( $modelArray ) {
    	$this->_properties = [];
    	foreach( static::$_fields as $field ) {
    		if ( isset( $modelArray[$field] ) ) {
    			$this->_properties[$field] = $modelArray[$field];
    		}
    	}

        if ( isset( $this->_properties[static::INDEX] ) ) $this->_new = false;
    }

    protected function setObject( $model ) {
        $this->_properties = [];
        foreach( static::$_fields as $field ) {
            if ( isset( $model->$field ) ) {
                $this->_properties[$field] = $model->$field;
            }
        }

        if ( isset( $this->_properties[static::INDEX] ) ) $this->_new = false;
    }

    public static function find( $query ) {
        $class = static::CLASSNAME;
        return [ new $class() ];
    }

    public static function findRelated( $model, $query = null ) {
    	$class = static::CLASSNAME;
        return [ new $class() ];
    }

    protected function saveFilter() {
        return true;
    }

    protected function saveValidate() {
        return true;
    }

    protected function saveRecord() {
        return true;
    }

    public function save() {
    	$this->saveFilter();
        $this->saveValidate();
        $this->saveRecord();
    }

    protected function deleteValidate() {
        return true;
    }

    protected function deleteRecord() {
        return true;
    }

    public function delete() {
    	$this->deleteValidate();
        $this->deleteRecord();
    }

    public function getIndexValue() {
        if ( isset( $this->_properties[static::INDEX] ) ) {
            return $this->_properties[static::INDEX];
        }

        return null;
    }

    public function __set( $name, $value ) {
    	if ( in_array( $name, static::$_fields ) ) {
    		$this->_properties[$name] = $value;
    	}
    }

    public function __get( $name ) {
    	if ( in_array( $name, static::$_fields ) && isset( $this->_properties[$name] ) ) {
    		return $this->_properties[$name];
    	}

        return null;
    }

    public function __isset( $name ) {
    	return ( in_array( $name, static::$_fields ) && isset( $this->_properties[$name] ) );
    	
    }

    protected function filterJson( $fields ) {
        return $fields;
    }

    public function jsonSerialize() {
        return $this->filterJson( $this->_properties );
    }

}