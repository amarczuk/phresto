<?php
  
class Project {
    
    public $properties = [];
    public $codes = [];
    public $commentsPerPage = 15;
    protected $new = false;
    protected $codesIdx = [];
    
    public function __construct( $id = null, $title = null ) {
        global $User;

        if ( empty( $id ) && empty( $title ) ) {
            $this->properties = [ 'id' => '', 'title' => '', 'editors' => '' ];
            $this->new = true;
            return;
        }
        
        if ( !empty( $id ) ) {
            $this->_getById( $id );
        } else if ( !empty( $title ) ) {
            $this->_getByTitle( $title );
        }

        if ( empty( $this->properties['owner'] ) && $this->isEditable() && !$User->isSu() ) {
            $this->createOwner();
        }
    }

    public function create( $name, $empty = false ) {
        global $Config;
        global $User;

        if ( empty( $name ) ) {
            return 'Invalid sandbox name';
        }

        $title = dechex( time() - 361865400 ) . dechex( rand( 0, 1000 ) );
        if ( $User->isLog() ) {
            $colval = $User->id;
            $col = 'owner';
        } else {
            $colval = substr(  md5( str_shuffle( $title ) . rand( 1, 100 ) ), 6, 10 );
            $col = 'key';
        }
        
        $db = $Config->maindb;
        $sql = "insert 
                into    {$Config->dbpref}project 
                        ( title, name, d_added, d_seen, public, noclone, `{$col}` )
                values  ( '" . $db->escape( $title ) . "',
                          '" . $db->escape( strip_tags( $name ) ) . "',
                          " . time() . ",
                          0,
                          1,
                          0,
                          '" . $db->escape( $colval ) . "'
                        )";

        $db->query( $sql );
        $this->_getById( $db->lastid() );

        if ( $col == 'key' ) {
            $this->addKey( $colval );
        }

        if ( !$empty ) {
            $this->addCode( 'test.php', 
                            'php', 
                            "<?php\n", 
                            '{"postdata":"","getdata":"","version":"5.6"}' );
            $this->properties['editors'] = $this->codes['test.php']->properties['id'];
            $this->save();
        }

        return true;
    }
    
    public function makeClone( $ref = null ) {

        $copyCodes = [];
        if ( !empty( $ref ) ) {
            $embed = new Embed( $ref );
            if ( !empty( $embed->id ) && !empty( $embed->codes ) && $embed->projectid == $this->properties['id'] ) {
                $copyCodes = explode( ',', $embed->codes );
            }
        }

        if ( empty( $this->properties['id'] ) ) {
            return 'Invalid project';
        }

        if ( $this->properties['noclone'] == 1 ) {
            return 'Clonning locked';
        }

        $newProject = new Project();
        $newProject->create( $this->properties['name'] . ' clone', true );
        $newProject->properties['public'] = $this->properties['public'];
        $newProject->properties['description'] = $this->properties['description'];
        
        $newProject->save();

        $this->loadCodes();
        foreach ( $this->codes as $code ) {
            if ( empty( $copyCodes ) || in_array( $code->properties['id'], $copyCodes ) ) {
                $newProject->addCode( $code->properties['name'], 
                                      $code->properties['type'], 
                                      $code->properties['code'], 
                                      $code->properties['options'] );
            }
        }
        
        $tags = $this->getTags();
        foreach ( $tags['tags'] as $tag ) {
            $tTag = new Tag( null, null, $tag );
            $newProject->addTag( $tTag );
        }

        $editors = [];
        if ( !empty( $copyCodes ) ) {
            foreach ( $newProject->codes as $code ) {
                $editors[] = $code->properties['id'];
            }
        }

        $newProject->properties['editors'] = implode( ',', $editors );
        $newProject->save();
        
        return $newProject;
    }
    
    public function addCode( $name, $type, $codes, $options = null ) {
        $code = new Code( $this->properties['id'] );
        $code->properties['name'] = trim( strip_tags( $name ) );
        $code->properties['type'] = trim( strip_tags( $type ) );
        $code->properties['code'] = $codes;
        
        if ( !empty( $options ) ) {
            $code->properties['options'] = $options;
        }
        
        $res = $code->save();
        $this->codes[$code->properties['name']] = $code;
        return $res;
    }
    
    public function saveCodes( $save ) {
        global $Config;
        foreach ( $save['codes'] as $code ) {
            if ( !is_array( $code ) || !isset( $code['name'] ) ) continue;
            $this->codes[ $code['name'] ]->properties['code'] = $code['code'];
            $this->codes[ $code['name'] ]->save();
        }
        $editors = implode( ',', $save['visible'] );
        $sql = "update {$Config->dbpref}project set editors = '" . $Config->maindb->escape( $editors ) . "' where id = {$this->properties['id']} limit 1";
        $Config->maindb->query( $sql, 'project' );
    }
    
    public function save() {
        
        if ( empty( $this->properties['id'] ) ) return array( 'Wrong project id' );

        global $Config;
        $db = $Config->maindb;
        $pr = $this->properties;

        $sql = "update {$Config->dbpref}project 
                set 
                    editors = '" . $db->escape( $pr['editors'] ) . "', 
                    title = '" . $db->escape( $pr['title'] ) . "', 
                    name = '" . $db->escape( $pr['name'] ) . "', 
                    description = '" . $db->escape( $pr['description'] ) . "', 
                    d_seen = '" . $db->escape( $pr['d_seen'] ) . "', 
                    public = '" . $db->escape( $pr['public'] ) . "', 
                    noclone = '" . $db->escape( $pr['noclone'] ) . "', 
                    views = " . $db->escape( $pr['views'] ) . ", 
                    owner = '" . $db->escape( $pr['owner'] ) . "'
                where id = {$pr['id']} limit 1";

        $db->query( $sql, 'project' );
        
        return false;
    }

    public function delete() {
        if ( empty( $this->properties['id'] ) ) return array( 'Wrong project id' );

        global $Config;
        $sql = "delete from {$Config->dbpref}project where id = {$this->properties['id']} limit 1"; 
        $Config->maindb->query( $sql, 'project' );
    }

    protected function _getById( $id ) {
        global $Config;
        $sql = "select * from {$Config->dbpref}project where id = {$id} limit 1"; //poprawic dodac bind vars
        $Config->maindb->query( $sql, 'project' );
        $this->properties = $Config->maindb->assoc( 'project' );
    }
    
    protected function _getByTitle( $title ) {
        global $Config;
        $sql = "select * from {$Config->dbpref}project where title = '" . $Config->maindb->escape($title) . "' limit 1";
        $Config->maindb->query( $sql, 'project' );
        $this->properties = $Config->maindb->assoc( 'project' );
    }
    
    
    public function loadCodes() {
        global $Config;
        
        $this->codes = array();
        $sql = "select * from {$Config->dbpref}code where project = {$this->properties['id']}"; //poprawic dodac bind vars
        $Config->maindb->query( $sql, 'project' );
        while ( $code = $Config->maindb->assoc( 'project' ) ) {
            $this->codes[$code['name']] = new Code( $this->properties['id'], null, null, $code );
            $this->codesIdx[$code['id']] = $code['name'];
        }
    }
    
    public function getEditors() {
        
        $editors = array();
        
        foreach ( $this->codes as $code ) {
            $editors[] = $this->getEditor( $code->properties['name'] );
        }
        
        return $editors;
    }
    
    public function getEditor( $name ) {
        
        $code = $this->codes[$name];
        return array( 'id' => 'code_' . $code->properties['id'], 
                      'name' => $code->properties['name'], 
                      'mode' => $code->properties['type'], 
                      'codeid' => $code->properties['id'], 
                      'code' => $code->properties['code'], 
                      'options' => $code->properties['options'] 
                    );
        
    }

    public function isEditable() {
        global $User;
        global $Config;

        if ( empty( $_SESSION['projectKey'] ) ) {
            $_SESSION['projectKey'] = [];
        }

        if ( !empty( $_SESSION['projectKey'][$this->properties['id']] ) && $_SESSION['projectKey'][$this->properties['id']] == $this->properties['key'] ) {
            return true;
        }

        if ( !$User->isLog() ) {
            return false;
        }

        if ( $User->isSu() ) {
            return true;
        }

        if ( !empty( $User->id ) && $User->id == $this->properties['owner'] ) {
            return true;
        }

        $db = $Config->maindb;

        $sql = "SELECT count(userid) AS cnt 
                FROM   {$Config->dbpref}user_project
                WHERE  projectid = {$this->properties['id']}
                  AND  userid = {$User->id}";
        $db->query( $sql, 'project' );
        $tmp = $db->assoc( 'project' );

        if ( $tmp['cnt'] > 0 ) {
            return true;
        }

        return false;
    }

    public function isOwner() {
        global $User;
        global $Config;


        if ( !$User->isLog() ) {
            return false;
        }

        if ( $User->isSu() ) {
            return true;
        }

        if ( !empty( $User->id ) && $User->id == $this->properties['owner'] ) {
            return true;
        }

        return false;
    }

    public function getUsage() {
        return array( 'codes' => count( $this->codes ), 
                      'codes_limit' => $this->getFilesLimit(),
                      'expiry_date' => date( 'd/m/Y', $this->getExpiryDate() ),
                      'views' => $this->properties['views']
                    );
    }

    public function getUsers() {
        global $Config;
        $db = $Config->maindb;
        
        $sql = "select u.id, u.nick, u.name from {$Config->dbpref}user_project up, {$Config->dbpref}user u 
                where u.id = up.userid AND up.projectid = {$this->properties['id']} order by u.nick";
        $db->query( $sql, 'project_users' );
        $users = array();
        while ( $user = $db->assoc( 'project_users' ) ) {
            $user['pic'] = User::getUserPicture( $user['id'] );
            $users[] = $user;
        }
        
        return array( 'users' => $users );
    }

    public function addUser( $email ) {
        global $Config;
        $db = $Config->maindb;
        
        $sql = "INSERT 
                INTO {$Config->dbpref}user_project ( userid, projectid ) 
                VALUES ( (  SELECT id 
                            FROM {$Config->dbpref}user
                            WHERE email = '" . $db->escape( $email ) . "'
                            LIMIT 1
                         ),
                         {$this->properties['id']} )";
        if( !$db->query( $sql, 'project_users' ) ) {
            return array( 'can\'t add this user');
        }
        
        
        return false;
    }

    public function delUser( $id ) {
        global $Config;
        $db = $Config->maindb;
        
        $sql = "delete 
                from {$Config->dbpref}user_project
                where   userid = " . $db->escape( $id ) . " and
                        projectid = {$this->properties['id']}
                limit 1";
        $db->query( $sql, 'project_users' );
        
        return false;
    }
    
    public function getTags() {
        global $Config;
        $db = $Config->maindb;
        
        $sql = "select t.id, t.name from {$Config->dbpref}tag_project tp, {$Config->dbpref}tag t 
                where t.id = tp.tagid AND tp.projectid = {$this->properties['id']} order by t.name";
        $db->query( $sql, 'project_tags' );
        $tags = array();
        while ( $tag = $db->assoc( 'project_tags' ) ) {
            $tags[] = $tag;
        }
        
        return array( 'tags' => $tags );
    }

    public function getEmbeds() {
        $embed = new Embed();

        $embeds = $embed->search( [ 'where' => [ 'projectid' => $this->properties['id'] ] ] )['properties'];
        
        foreach ( $embeds as $key => $value ) {
            $tmp = explode( ',', $value['codes'] );
            $embeds[$key]['code_names'] = '';
            foreach ( $tmp as $codeid ) {
                $embeds[$key]['code_names'] .= $this->codesIdx[$codeid] . ' ';
            }
            $embeds[$key]['main_code_name'] = $this->codesIdx[$value['main_code']];
        }

        return $embeds;
    }

    public function saveEmbed( Embed $embed ) {
        $embed->projectid = $this->properties['id'];
        return $embed->save();
    }

    public function getFilesLimit() {
        return 15;
    }
    
    public function getExpiryDate() {
        if ( !empty( $this->properties['owner'] ) ) {
            return  $this->properties['d_seen'] + ( 60 * 60 * 24 * 31 );
        } else {
            return  $this->properties['d_seen'] + ( 60 * 60 * 24 * 7 );
        }
    }

    public function getDetails() {
        $details = $this->properties;
        if ( empty( $details['name'] ) ) {
            $details['name'] = 'New Project';
        }

        if ( empty( $details['owner'] ) ) {
            $details['owner_name'] = 'no owner';
        } else {
            $owner = new UserObj( $details['owner'] );
            $details['owner_name'] = $owner->nick;
        }
        $details['date_added'] = date( 'd/m/Y', $this->properties['d_added'] );
        $details['date_opened'] = date( 'd/m/Y H:i', $this->properties['d_seen'] );
        $details['insert_code'] = 1;
        $details['is_editable'] = ( $this->isEditable() ) ? 1 : 0;

        if ( $this->isEditable() || !empty( $this->properties['owner'] ) ) {
            $details['insert_code'] = 0;
        }

        return $details;
    }

    public function addTag( $tag ) {
        global $Config;
        $db = $Config->maindb;

        $sql = "insert 
                into {$Config->dbpref}tag_project ( tagid, projectid )
                values ( {$tag->properties['id']}, {$this->properties['id']} )";

        if ( $db->query( $sql, 'project_tag' ) ) {
            return true;
        }

        return array( 'Tag already exists' );
    }

    public function removeTag( $tag ) {
        global $Config;
        $db = $Config->maindb;

        $sql = "delete 
                from {$Config->dbpref}tag_project
                where tagid = {$tag->properties['id']}
                  and projectid = {$this->properties['id']} 
                limit 1";

        $db->query( $sql );
        return true;
    }
    
    public function getComments( $page = null ) {
        if ( !$this->properties['id'] ) return;
        $pp = $this->commentsPerPage;
        global $Config;
        $db = $Config->maindb;
        if ( !$page ) $page = 1;
        $start = ( $page - 1 ) * $pp;

        $coms = array( 'comments' => array() );
        $sql = "select * 
                from {$Config->dbpref}comments
                where projectid = {$this->properties['id']}
                order by date desc
                limit {$start}, {$pp}";
        $db->query( $sql, 'project_com' );
        while ( $tmp = $db->assoc( 'project_com' ) ) {
            $tc =  new Comment( null, $tmp );
            $coms['comments'][] = $tc->get();
        }

        $sql = "select count(id) as cnt
                from {$Config->dbpref}comments
                where projectid = {$this->properties['id']}";

        $db->query( $sql, 'project_com' );
        $tmp = $db->assoc( 'project_com' );

        $pgc = ceil( $tmp['cnt'] / $pp );

        if ( $pgc > $page ) {
            $coms['next'] = $page + 1;
            $coms['next_cls'] = '';
        } else {
            $coms['next'] = $page;
            $coms['next_cls'] = 'hide';
        }
        
        return $coms;
    }

    public function addComment( $comment ) {
        $cm = new Comment();
        $cm->properties['projectid'] = $this->properties['id'];
        $cm->properties['comment'] = $comment;
        $res = $cm->save();

        if ( $res === true ) {
            return $cm;
        }

        return $res;
    }

    public function addKey( $key ) {

        if ( !empty( $this->properties['key'] ) && $key == $this->properties['key'] ) {
            if ( empty( $_SESSION['projectKey'] ) ) $_SESSION['projectKey'] = [];
            $_SESSION['projectKey'][$this->properties['id']] = $key;
            $this->createOwner();
            return true;
        }

        return array( 'Wrong access key' );
    }

    protected function createOwner() {
        global $User;
        global $Config;

        if ( !$User->isLog() || !empty( $this->properties['owner'] ) ) return;

        $db = $Config->maindb;

        $sql = "UPDATE {$Config->dbpref}project
                SET owner = {$User->id},
                    `key` = ''
                WHERE id = {$this->properties['id']}
                LIMIT 1";
        $db->query( $sql );

        $_SESSION['projectKey'][$this->properties['id']] = '';

    }

    public function isVisible() {
        if ( $this->properties['public'] == 1 ) {
            return true;
        }

        if ( $this->isEditable() ) {
            return true;
        }

        return false;
    }
}
