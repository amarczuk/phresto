<?php

namespace Phresto;
use Phresto\Model;

class MySQLModel extends Model {

    const CLASSNAME = __CLASS__;

    const DB = 'mysql';
    const NAME = 'model';
    const INDEX = 'id';
    const COLLECTION = 'model';

    public static function find( $query ) {
    	$db = MySQLConnector::getInstance( static::DB );

    	$conds = [];
        $binds = [];
        $i = 0;

    	foreach ( $query['where'] as $key => $val ) {
    		if ( in_array( $key, static::$_fields ) ) {
    			$sql = $key . ' = :val' . $i;
                $binds['val' . $i] = $val;
    			$conds[] = $sql;
    		}
        }

		if ( empty( $conds ) ) {
			$conds = [ '1' ];
		}

    	$sql = "SELECT * FROM " . static::COLLECTION . " WHERE " . implode( ' AND ', $conds );
    	if ( isset( $query['limit'] ) ) {
    		$sql .= ' LIMIT ' . $query['limit'];
    	}

    	$result = $db->query( $sql, $binds );

    	while ( $row = $db->getNext( $result ) ) {
    		$res[] = $row;
    	}

    	return $res;
    }

    public static function findRelated( $model, $query = null ) {
        $class = static::CLASSNAME;
        return [ new $class() ];
    }

    protected function saveRecord() {
    	$db = MySQLConnector::getInstance( static::DB );

    	if ( !$this->_new ) {
            $fields = [];
            foreach ( $this->_properties as $key => $value) {
                $fields[] = $key . ' = :' . $key;
            }
    		$sql = "UPDATE " . static::COLLECTION . " SET " . implode( ', ', $fields );
    		$sql .= " WHERE " . static::INDEX . " = :" . static::INDEX . " LIMIT 1";
    	} else {
    		$sql = "INSERT INTO " . static::COLLECTION . " ( `" . implode( '`, `', static::$_fields ) . "` ) ";
    		$sql .= "VALUES ( " . implode( ', :', static::$_fields ) . " )";
    	}

    	$db->query( $sql, $this->_properties );

    	if ( $this->_new ) {
    		$this->_new = false;
    		$this->getById( $db->getLastId() );
    	}

    	return true;
    }

    protected function deleteRecord() {
    	$db = MySQLConnector::getInstance( self::DB );

        $sql = "delete from " . static::COLLECTION . " where " . static::INDEX . " = :index limit 1";
        $bind = [ 'index' => $this->_properties[static::INDEX] ];
        $db->query( $sql, $bind );
        
        $this->getEmpty();
        return true;
    }

}