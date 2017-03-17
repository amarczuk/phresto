<?php
  
class Code {
    
    public $properties = array();
    protected $project;
    protected $new = false;
    
    public function __construct( $project, $id = null, $name = null, $code = null ) {
        
        $this->project = $project;
        
        if ( empty( $id ) && empty( $name )  && empty( $code ) ) {
            $this->properties = array( 'id' => '', 'name' => 'new', 'type' => 'unknown', 'code' => '', 'options' => '', 'project' => $this->project );
            $this->new = true;
        }
        
        if ( !empty( $id ) ) {
            $this->_getById( $id );
            return;
        }
        
        if ( !empty( $name ) ) {
            $this->_getByName( $name );
            return;
        }
        
        if ( !empty( $code ) ) {
            $this->properties = $code;
            $this->process();
        }
    }
    
    public function save() {
        global $Config; 
        
        $path_parts = pathinfo( $this->properties['name'] );
        $name = str_replace( '.' . $path_parts['extension'], '', $path_parts['basename'] );
        if ( $name == '' || $name == 'new' ) return LAN_CODE_ERR_FILE_NAME;
        if ( $this->properties['type'] == '' || $this->properties['type'] == 'unknown' ) return LAN_CODE_ERR_FILE_TYPE;
        if ( $this->properties['id'] == '' ) {

            $sql = "select id 
                        from {$Config->dbpref}code 
                        where 
                            name = '" . $Config->maindb->escape( $this->properties['name'] ) . "' and 
                            project='{$this->project}' 
                        limit 1";
            $Config->maindb->query( $sql, 'code' );
            if ( $Config->maindb->numrows( 'code' ) > 0 ) {
                return LAN_CODE_ERR_FILE_EXISTS;
            }

            $sql = "insert into {$Config->dbpref}code ( project, name, type, code, options ) 
                        VALUES ( '{$this->project}', 
                                 '" . $Config->maindb->escape( $this->properties['name'] ) . "', 
                                 '" . $Config->maindb->escape( $this->properties['type'] ) . "', 
                                 '" . $Config->maindb->escape( $this->properties['code'] ) . "', 
                                 '" . $Config->maindb->escape( $this->properties['options'] ) . "' )";
            $Config->maindb->query( $sql, 'code' );
            $this->properties['id'] = $Config->maindb->lastid();
            $this->new = false;
        } else {
            $sql = "update {$Config->dbpref}code 
                        set 
                            code = '" . $Config->maindb->escape( $this->properties['code'] ) . "',
                            options = '" . $Config->maindb->escape( $this->properties['options'] ) . "' 
                        where id = {$this->properties['id']}"; 
            $Config->maindb->query( $sql, 'code' );
        }
        
        return true;
    }
    
    public function delete() {
        global $Config; 
        
        $sql = "delete from {$Config->dbpref}code where id = {$this->properties['id']} limit 1";
        $Config->maindb->query( $sql, 'code' );
        
        return true;
    }
    
    protected function _getById( $id ) {
        global $Config;
        $sql = "select * from {$Config->dbpref}code where id = {$id}"; //poprawic dodac bind vars
        $Config->maindb->query( $sql, 'code' );
        $this->properties = $Config->maindb->assoc( 'code' );
        $this->project = $this->properties['project'];
        $this->process();
    }
    
    protected function _getByName( $name ) {
        global $Config;
        $sql = "select * 
                from {$Config->dbpref}code 
                where 
                    name = '" . $Config->maindb->escape( $name ) . "' and 
                    project = {$this->project} limit 1"; 
        $Config->maindb->query( $sql, 'code' );
        $this->properties = $Config->maindb->assoc( 'code' );
        $this->process();
    }

    protected function process() {
        $this->properties['code'] = preg_replace( "/\<[\s]*\/[\s]*textarea[\s]*\>/isU", '---textarea---', $this->properties['code'] );
    }

    public function getCode() {
        return str_replace( '---textarea---', '</textarea>', $this->properties['code'] );
    }
    
}