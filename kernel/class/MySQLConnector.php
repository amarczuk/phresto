<?php

namespace Phresto;
use Phresto\Exception\DBException;

class MySQLConnector extends DBConnector
{

    const CLASSNAME = __CLASS__;
    
    public function connect( $options ) {
        $db = @Container::mysqli( $options['host'], $options['user'], $options['passwd'], $options['dbname'] );

        if ( $db->connect_error ) {
            return false;
        }

        if ( !empty( $options['names'] ) ) {
            $db->query( "SET NAMES utf8" );
        } else {
            $db->query( "SET NAMES " . $this->escape( $options['name']) );
        }

        return $db;
    }
    
    public function close() {
        $this->connection->close(); 
    }
    
    public function escape( $var ) {
        if ( empty( $var ) ) {
            return "''";
        }

        if ( is_string( $var ) ) {
            return "'" . $this->connection->real_escape_string( $var ) . "'";
        }

        if ( is_array( $var ) ) {
            foreach ( $var as $key => $val ) {
                $var[$key] = $this->escape( $val );
            }
            return '(' . implode( ', ', array_values( $val ) ) . ')';
        }

        if ( is_bool( $var ) ) {
            return ( $var ) ? 'TRUE' : 'FALSE';
        }

        if ( is_numeric( $var ) ) {
            return $var;
        }

        throw new DBException( "Provided type is not supported" );
        
    }
    
    public function bind( $query, $variables ) {
        foreach ( $variables as $key => $val ) {
            $val = $this->escape( $val );
            $query = str_replace( ':' . $key, $val, $query );
        }

        return $query;
    }

    public function query( $query, $bindings = [] ) {
        if ( !empty( $bindings ) ) {
            $query = $this->bind( $query, $bindings );
        }

        if ( !$result = $this->connection->query( $query ) ) {
            throw new DBException( "Query failed: " . $this->getLastError() );
            
        }
        return $result;
    }

    public function count( $resource ) {
        return $resource->num_rows;
    }
    
    public function getNext( $resource ) {
        return $resource->fetch_assoc();
    }

    public function getLastId() {
        return $this->connection->insert_id;
    }

    public function getLastError() {
        return $this->connection->error;
    }

}