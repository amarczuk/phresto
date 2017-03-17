<?php
  
class Tag {
    
    public $properties = array();
    protected $new = false;
    
    public function __construct( $id = null, $name = null, $tag = null ) {
        
        if ( empty( $id ) && empty( $name )  && empty( $tag ) ) {
            $this->properties = array( 'id' => '', 'name' => '' );
            $this->new = true;
            return;
        }
        
        if ( !empty( $id ) ) {
            $this->_getById( $id );
            return;
        }
        
        if ( !empty( $name ) ) {
            $this->_getByName( $name );
            return;
        }
        
        if ( !empty( $tag ) ) {
            $this->properties = $tag;
        }
    }
    
    public function save() {
        global $Config; 
        
        if ( $this->properties['name'] == '' ) return 'Incorrect tag name';
        if ( $this->properties['id'] == '' ) {

            $sql = "select id 
                        from {$Config->dbpref}tag 
                        where 
                            name = '" . $Config->maindb->escape( $this->properties['name'] ) . "'
                        limit 1";
            $Config->maindb->query( $sql, 'tag' );
            if ( $Config->maindb->numrows( 'tag' ) > 0 ) {
                return 'Tag already exists';
            }

            $sql = "insert into {$Config->dbpref}tag ( name ) 
                        VALUES ( '" . $Config->maindb->escape( $this->properties['name'] ) . "' )";
            $Config->maindb->query( $sql, 'tag' );
            $this->properties['id'] = $Config->maindb->lastid();
            $this->new = false;

        } else {
            $sql = "update {$Config->dbpref}tag
                        set 
                            name = '" . $Config->maindb->escape( $this->properties['name'] ) . "'
                        where id = {$this->properties['id']}"; 
            $Config->maindb->query( $sql, 'tag' );
        }
        
        return true;
    }
    
    public function delete() {
        global $Config; 
        
        $sql = "delete from {$Config->dbpref}tag where id = {$this->properties['id']} limit 1";
        $Config->maindb->query( $sql, 'tag' );
        
        return true;
    }
    
    protected function _getById( $id ) {
        global $Config;
        $sql = "select * from {$Config->dbpref}tag where id = {$id}"; //poprawic dodac bind vars
        $Config->maindb->query( $sql, 'tag' );
        $this->properties = $Config->maindb->assoc( 'tag' );
    }
    
    protected function _getByName( $name ) {
        global $Config;
        $sql = "select * 
                from {$Config->dbpref}tag 
                where 
                    name = '" . $Config->maindb->escape( $name ) . "' 
                limit 1";
        $Config->maindb->query( $sql, 'tag' );
        $this->properties = $Config->maindb->assoc( 'tag' );
    }
    
}