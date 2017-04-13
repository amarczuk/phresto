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
    protected $_debug = '';
    /**
    * array of the model field names (as in db)
    */
    protected static $_fields = [];
    /**
    * array default field values (['field_name' => 'default value'])
    * if value should be calculated during runtime leave value empty and
    * add protected function `default_field_name()` returning default value
    */
	protected static $_defaults = [];
    /**
    * array describes model relations:
    * 'model_name' => [ // key is the related model name
    *        'type' => '1:n', // 1:1, 1:n, n:1, n:n - first model second related model
    *        'model' => 'model_name', // related model name
    *        'field' => 'field_in_related_model',  // name of the FK in related model
    *        'index' => 'id', // index (FK) in the model
    *        'junction' => [ // junction table description for n:n relations
    *           'collection' => 'junction_table', // junction table name
    *           'field' => 'related_model_fk', // related model FK
    *           'index' => 'model_fk' // model FK
    *        ]
    *    ]
    */
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
            $this->setById( $option );
        }

        $this->_initial = $this->_properties;
    }

    public static function auth( $reqType ) {
        return true;
    }

    public static function getIndexField() {
        return static::INDEX;
    }

    public static function getName() {
        return static::NAME;
    }

    public static function getCollection() {
        return static::COLLECTION;
    }

    public static function isRelated( $modelName ) {
        return array_key_exists( $modelName, static::$_relations );
    }

    public static function getRelation( $modelName ) {
        return static::$_relations[$modelName];
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

    public function setById( $id ) {
        $this->setObject( static::find( [ 'where' => [ static::INDEX => $id ], 'limit' => 1 ] )[0] );
    }

    public function setRelatedById( $model, $id ) {
        $this->setObject( static::findRelated( $model, [ 'where' => [ static::INDEX => $id ], 'limit' => 1 ] )[0] );
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

    public static function findRelated( Model $model, $query = null ) {
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

    public function getIndex() {
        if ( isset( $this->_properties[static::INDEX] ) ) {
            return $this->_properties[static::INDEX];
        }

        return null;
    }

    public function __set( $name, $value ) {
        if ( $name == '_debug_' ) {
            return $this->_debug = $value;
        }

    	if ( in_array( $name, static::$_fields ) ) {
    		$this->_properties[$name] = $value;
    	}
    }

    public function __get( $name ) {
        if ( $name == '_debug_' ) {
            return $this->_debug;
        }

    	if ( in_array( $name, static::$_fields ) && isset( $this->_properties[$name] ) ) {
    		return $this->_properties[$name];
    	}

        return null;
    }

    public function __isset( $name ) {
        $debug = ( $name == '_debug_' && !empty( $this->_debug ) );
    	return ( $debug || ( in_array( $name, static::$_fields ) && isset( $this->_properties[$name] ) ) );
    	
    }

    protected function filterJson( $fields ) {
        return $fields;
    }

    public function jsonSerialize() {
        $fields = $this->filterJson( $this->_properties );
        if ( !empty( $this->_debug ) ) {
            $fields['_debug_'] = $this->_debug;
        }
        return $fields;
    }

}