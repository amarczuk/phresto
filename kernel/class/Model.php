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
    protected $_initial = [];
    protected static $_fields = [];
	protected static $_defaults = [];
    protected static $_relations = [];
    protected $_new = true;

    public function __construct( $option = null ) {
        
        if ( empty( $option ) ) {
            $this->getEmpty();
        } else if ( is_array( $option ) && isset( $option['where'] ) ) {
            $option['limit'] = 1;
            $this->setObject( self::find( $option )[0] );
        } else if ( is_array( $option ) ) {
            $this->set( $option );
        } else if ( is_object( $option ) ) {
            $this->setObject( $option );
        } else if ( is_string( $option ) && !is_numeric( $option) && $json = json_decode( $option, true ) ) {
            $this->set( $json );
        } else {
            $this->getById( $option );
        }

        $this->_initial = $this->_properties;
    }

    public static function auth( $reqType ) {
        return true;
    }

    public static function getIndexField() {
        return static::INDEX;
    }

    public static function isRelated( $modelName ) {
        return array_key_exists( $modelName, static::$_relations );
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
        $this->setObject( static::find( [ 'where' => [ static::INDEX => $id ], 'limit' => 1 ] )[0] );
    }

    public function update( $modelArray ) {
        foreach( static::$_fields as $field ) {
            if ( isset( $modelArray[$field] ) ) {
                $this->_properties[$field] = $modelArray[$field];
            }
        }

        if ( !empty( $this->_properties[static::INDEX] ) ) $this->_new = false;
    }

    protected function set( $modelArray ) {
    	$this->_properties = [];
    	foreach( static::$_fields as $field ) {
    		if ( isset( $modelArray[$field] ) ) {
    			$this->_properties[$field] = $modelArray[$field];
    		}
    	}

        if ( !empty( $this->_properties[static::INDEX] ) ) $this->_new = false;
    }

    protected function setObject( $model ) {
        $this->_properties = [];
        foreach( static::$_fields as $field ) {
            if ( isset( $model->$field ) ) {
                $this->_properties[$field] = $model->$field;
            }
        }

        if ( !empty( $this->_properties[static::INDEX] ) ) $this->_new = false;
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

    protected function saveAfter() {
        return true;
    }

    protected function saveSetDefaults() {
        if ( !$this->_new ) return true;
        foreach ( static::$_defaults as $key => $value ) {
            if ( empty( $this->$key ) ) {
                if ( empty( $value ) && method_exists( $this, 'default_' . $key ) ) {
                    $this->$key = $this->{'default_' . $key}();
                } else {
                    $this->$key = $value;
                }
            }
        }
    }

    public function save() {
        $this->saveSetDefaults();
    	$this->saveFilter();
        $this->saveValidate();
        $this->saveRecord();
        $this->_initial = $this->_properties;
        $this->saveAfter();
    }

    protected function deleteValidate() {
        return true;
    }

    protected function deleteRecord() {
        return true;
    }

    protected function deleteAfter() {
        return true;
    }

    public function delete() {
    	$this->deleteValidate();
        $this->deleteRecord();
        $this->_properties[static::INDEX] = null;
        $this->_initial = $this->_properties;
        $this->_new = true;
        $this->deleteAfter();
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