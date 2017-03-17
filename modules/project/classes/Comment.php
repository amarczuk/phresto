<?php
  
class Comment {
    
    public $properties = array();
    protected $new = false;
    
    public function __construct( $id = null, $data = null ) {
        
        if ( empty( $id ) && empty( $data ) ) {
            $this->properties = array( 'id' => '', 'comment' => '' );
            $this->new = true;
            return;
        }
        
        if ( !empty( $id ) ) {
            $this->_getById( $id );
            return;
        }
        
        if ( !empty( $data ) ) {
            $this->properties = $data;
        }
    }
    
    public function save() {
        global $Config; 
        global $User;

        if ( $this->properties['projectid'] == '' ) return array( 'Incorrect project' );

        $this->process();

        if ( trim( $this->properties['comment'] ) == '' ) return array( 'Incorrect content' );

        if ( $this->properties['id'] == '' ) {

            $userid = 'NULL';
            if ( $User->isLog() ) {
                $userid = $User->id;
            }

            $sql = "insert 
                    into {$Config->dbpref}comments 
                         ( userid, projectid, comment, date ) 
                    VALUES ( {$userid},
                             {$this->properties['projectid']},
                             '" . $Config->maindb->escape( $this->properties['comment'] ) . "',
                             " . time() . ")";

            $Config->maindb->query( $sql, 'comment' );
            $this->properties = array( 'id' => $Config->maindb->lastid() );
            $this->new = false;

            $this->_getById( $this->properties['id'] );

        } else {
            $sql = "update {$Config->dbpref}comments
                        set 
                            comment = '" . $Config->maindb->escape( $this->properties['comment'] ) . "'
                        where id = {$this->properties['id']}"; 
            $Config->maindb->query( $sql, 'comment' );
        }
        
        return true;
    }

    public function vote( $up = true ) {
        global $Config;

        if ( $up ) {
            $this->properties['vote']++;
        } else {
            $this->properties['vote']--;
        }

        $sql = "update {$Config->dbpref}comments
                set 
                    vote = {$this->properties['vote']}
                where id = {$this->properties['id']}"; 
        $Config->maindb->query( $sql, 'comment' );
    }
    
    public function delete() {
        global $Config; 
        
        $sql = "delete from {$Config->dbpref}comments where id = {$this->properties['id']} limit 1";
        $Config->maindb->query( $sql, 'comment' );
        
        return true;
    }
    
    protected function _getById( $id ) {
        global $Config;
        $sql = "select * from {$Config->dbpref}comments where id = {$id}"; //poprawic dodac bind vars
        $Config->maindb->query( $sql, 'comment' );
        $this->properties = $Config->maindb->assoc( 'comment' );
    }
    
    protected function process() {
        $src = $this->properties['comment'];

        $found = [];
        $pattern = '#\<code(?P<type>.*)>(?P<code>.*)\</code>#isU';
        preg_match_all( $pattern, $src, $found );

        foreach ( $found['code'] as $key => $code ) {
            $type = '';
            $ttype = trim( $found['type'][$key] );
            if ( !empty( $ttype ) ) {
                $ttype = str_replace( ' ', '', $ttype );
                $ttype = str_replace( ['type=', '"', "'"], '', $ttype );
                $type = trim( $ttype );
            }
            $after = '<tmpcode class="comsCode ' . $type . '">' . trim( str_replace( '<', '&lt;', $code ) ) . '</tmpcode>';
            $src = str_replace( $found[0][$key], $after, $src );
        }
        $tags = ['a','img','b','i','u','blockquote','ul','li','ol','tmpcode'];
        $src = strip_tags( $src, '<' . implode( '><', $tags ) . '>' );
        $src = str_replace( ['<tmpcode', '</tmpcode'], ['<code', '</code'], $src );
        $src = Template::closeTags( $src, $tags );
        $src = nl2br( $src, false );
        $this->properties['comment'] = trim( $src );
    }

    public function get() {
        $cm = $this->properties;
        $cm['fdate'] = date( 'Y/m/d H:i', $this->properties['date'] );
        $cm['upic'] = User::getDefaultPicture();
        
        if ( empty( $cm['userid'] ) ) {
            $cm['user'] = 'Anonymous';
        } else {
            $usr = new User( $cm['userid'] );
            $cm['user'] = $usr->nick;
            $cm['upic'] = $usr->getPicture();
        }

        return $cm;
    }
    
}