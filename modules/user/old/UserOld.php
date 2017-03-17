<?php

class User 
{
	
	public $id = 0;
	public $email;
	public $name;
	public $nick;
	public $d_payment;
	public $status;
	public $d_added;
	public $d_logged;
	public $emailEditable;
	
	private $loggedin = false;

	public function __construct( $id = null )
	{
		if ( !empty( $id ) ) {
			$this->load( $id );
		}
	}
	
	
	public function __sleep()
	{
		return array( 'id', 'loggedin' );
	}
	
	
	public function __wakeup()
	{	
		if ( $this->id > 0 ) $this->load( $this->id );
	}
	
	
	public function load( $id )
	{
		global $Config;
		$db = $Config->maindb;

		if  ( $id <= 0 ) return false;
		
		$sql = "select * from {$Config->dbpref}user where id = '{$id}' limit 1";
		
		$db->query( $sql, 'user' );
		if ( $db->numrows( 'user' ) <= 0 ) return false;
		
		$usr = $db->fetch('user');
		
		$this->id = $id;
		$this->name = $usr['name'];
		$this->email = $usr['email'];
		$this->nick = $usr['nick'];
		$this->d_payment = $usr['d_payment'];
		$this->status = $usr['status'];
		$this->d_added = $usr['d_added'];
		$this->d_logged = $usr['d_logged'];
		$this->emailEditable = true;
		if ( $urs['pass'] = md5( md5( '' ) ) ) {
			$this->emailEditable = false;
		}
		
		return true;
		
	}
	
	public function isLog( $returnString = false )
	{
		
		if ($this->loggedin) return ( $returnString ) ? '1' : true;
		
		return ( $returnString ) ? '0' : false;
		
	}
	
	public function getDetails() {
		if ( empty( $this->id ) ) return array();

		return array( 'id' => $this->id,
					  'nick' => $this->nick,
					  'email' => $this->email,
					  'name' => $this->name,
					  'status' => $this->status,
					  'logged' => ( $this->loggedin ) ? 1 : 0,
					  'last_login' => date( 'd/m/Y H:i', $this->d_logged ),
					  'registered' => date( 'd/m/Y', $this->d_added ),
					  'pic' => $this->getPicture(),
					  'emailEditable' => ( $this->emailEditable ) ? 1 : 0 );
	}

	public function socialLogin( $user ) {
		global $Config;
		$db = $Config->maindb;

		$sql = "SELECT id from {$Config->dbpref}user 
				where email = '" . $db->escape( $user['email'] ) . "'
				limit 1";
		$db->query( $sql, 'user' );
		
		if ( $db->numrows( 'user' ) != 1 ) {
			$data = [];
			$data['email'] = $user['email'];
			$data['nick'] = $user['given_name'];
			$data['name'] = $user['name'];
			$data['pass'] = '';
			$data['nopass'] = true;
			$data['reg'] = 'ok';

			$this->add( $data );
			$db->query( $sql, 'user' );
			$tmp = $db->fetch('user');
			$id = $tmp['id'];

			if ( !empty( $user['raw_picture'] ) ) {
					$picture = $user['raw_picture'];
			} else {
				try {
					$picture = file_get_contents( $user['picture'] );
				} catch( Exception $e ) {}
			}

			if ( !empty( $picture ) ) {
				file_put_contents( 'public/user_icon/'  . $id . "_0.pic", $picture ); 
			}
		} else {
			$tmp = $db->fetch('user');
			$id = $tmp['id'];
		}
		

		$this->load( $id );
		$this->loggedin = true;
		$this->save();

		return true;
	}
	
	public function login( $email, $pass, $cookie = 0 )
	{
		global $Config;
	
		if ( $this->loggedin ) return true;
		if ( $email == '' || $pass == '' ) return false;
	
		$db = $Config->maindb;

		$sql = "SELECT id from {$Config->dbpref}user 
				where email = '" . $db->escape( $email ) . "' AND 
					  pass = '" . md5( md5( $pass ) ) . "' 
				limit 1";
		$db->query($sql, 'user');
		
		if ($db->numrows( 'user' ) != 1 ) return false;
		$tmp = $db->fetch('user');
		$id = $tmp['id'];
		
		if ( !$this->load( $id ) ) return false;
	
		$this->loggedin = true;

		$sql = "UPDATE {$Config->dbpref}user 
                SET  d_logged = ". time() .", 
              	WHERE
              		 id = {$this->id} 
              	limit 1";
		$db->query($sql, 'user');

		/*
		if ($cookie==1) 
		{
			$czas=time()+(60*60*24*1000);
			setcookie('zalog', "{$this->id}", $czas);
			setcookie('hash', md5($this->nick), $czas);
		};
		*/
		
		$this->save();
		
		return true;
	}
	

	public function logout()
	{
		/*
		$czas=time()-(60*600);
		setcookie('zalog', '0', $czas);
		setcookie('hash', '', $czas);
		*/
		
		$this->loggedin = false;
		
		$this->save();
		return true;
	}	
	
	// REJESTRACJA
	
	public function add( $data )
	{
		global $Config;

		$valid = $this->validate( $data );
		
		if ( $valid !== true )
		{
			return $valid;
		}
	
		$db = $Config->maindb;
		
		$time = time();
		
		$sql = "INSERT INTO {$Config->dbpref}user (email, status, pass, nick, name, d_added, d_logged )
                          VALUES ('" . $db->escape( $data['email'] ) . "', 
                          		  1, 
                          		  '" . md5( md5( $data['pass'] ) ) . "', 
                          		  '" . $db->escape( $data['nick'] ) . "', 
                          		  '" . $db->escape( $data['name'] ) . "', 
                          		  {$time}, 
                          		  {$time})";
						  
		$db->query( $sql, 'user' );
		$this->login( $data['email'], $data['pass'] );
		//$this->aktywacjaEmail();
		
		return true;
	
	
	
	}
	
	// save
	
	public function update( $data )
	{
        global $Config;
        $db = $Config->maindb;
        
		if ( !$this->loggedin ) return array( LAN_NOT_LOGGEDIN );
		if ( $this->id <= 0 ) return false;
		if ( $valid = $this->validate( $data ) !== true )
		{
			return $valid;
		}
		
		$sql = "UPDATE {$Config->dbpref}user 
                SET  email ='" . $db->escape( $data['email'] ) . "', ";
        if ( !empty( $data['pass'] ) ) $sql.= "pass = '" . md5( md5( $data['pass'] ) ) . "', ";
        $sql .= "    nick = '" . $db->escape( $data['nick'] ) . "', 
              		 name ='" . $db->escape( $data['name'] ) . "'
              	WHERE
              		 id = {$this->id} 
              	limit 1";
                
        $db->query( $sql, 'none' );
		
		$this->load( $this->id );

		if ( isset( $data['pic'] ) && !empty( $data['pic']['type'] ) ) {
			$types = [ 'image/jpeg', 'image/png', 'image/gif', 'image/jpg' ];
			if ( !in_array( $data['pic']['type'], $types ) ) {
				return [ 'Wrong type of image' ];
			}
			$no = $this->getPictureNo();
			if ( $no > -1 ) @unlink( 'public/user_icon/' . $this->id . "_{$no}.pic" );
			$no = ( $no < 10 ) ? $no + 1 : 0;
			move_uploaded_file( $data['pic']['tmp_name'], 'public/user_icon/'  . $this->id . "_{$no}.pic" );
		}

		return true;
	
	}

	public function delete()
	{
        global $Config;
        $db = $Config->maindb;
        
		if ( !$this->loggedin ) return array( LAN_NOT_LOGGEDIN );
		if ( $this->id <= 0 ) return false;
		
		$this->logout();

		$sql = "UPDATE {$Config->dbpref}comments
        		SET
        			userid = 0
              	WHERE
              		userid = {$this->id}";
                
        $db->query( $sql, 'none' );

        $sql = "UPDATE {$Config->dbpref}project
        		SET
        			owner = 0
              	WHERE
              		owner = {$this->id}";
                
        $db->query( $sql, 'none' );

		$sql = "DELETE FROM {$Config->dbpref}user 
              	WHERE
              		 id = {$this->id} 
              	limit 1";
                
        $db->query( $sql, 'none' );
		
		$this->id = 0;
		$this->save();

		return true;
	
	}
	
	private function validate( $data ) {
		global $Config;
		$db = $Config->maindb;

		if ( !ereg( '^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,10}$', $data['email'] ) ) {
			return array( LAN_ZLY_EMAIL );
		}
		
		if ( ( $data['pass'] == '' || $data['pass'] != $data['pass1'] ) && ( !isset( $data['nopass'] ) || !$data['nopass'] ) ) {
			return array( LAN_ZLE_HASLO );
		}
		
		if ( $data['reg'] != 'ok' ) {
			return array( LAN_ZLY_REG );
		}

		if ( trim( $data['nick'] ) == '' ) {
			return array( LAN_ZLY_NICK );
		}
		
        if ( $this->email != $data['email'] ) { 
        
		    $sql = "SELECT * FROM {$Config->dbpref}user 
				    WHERE email = '" . $db->escape( $data['email'] ) . "' 
				    LIMIT 1";
		    $db->query( $sql, 'user' );
		    
		    if ( $db->numrows( 'user' ) > 0 )
		    {
			    return array( LAN_ZLY_USER );					
		    }
        }

		return true;
	}
	
	public function save() {
		$_SESSION['USER'] = serialize( $this );
	}

	public static function getFromSession() {
		if ( isset( $_SESSION['USER'] ) ) {
			return unserialize( $_SESSION['USER'] );
		} else {
			$user = new User();
			$user->save();
			return $user;
		}
	}
	
	public function getLoginInfo() {
		$dane = array();
		$dane['request_uri'] = $_SERVER['REQUEST_URI'];
        if ( $this->isLog() ){
			$dane['zalogowany'] = '1';
			$dane['login'] = $this->email;
		} else {
			$dane['zalogowany'] = '0';
		}
		
		return $dane;
	}
    
    public function getProjects() {
        if ( !$this->id || !$this->isLog() ) {
            return array( 'projects' => array() );
        }
        
        global $Config;
        $db = $Config->maindb;
        
        $sql = "SELECT DISTINCT proj.id, proj.name, proj.title 
        		FROM {$Config->dbpref}project proj, {$Config->dbpref}user_project up 
        		WHERE ( up.userid = {$this->id} AND proj.id = up.projectid ) OR proj.owner = {$this->id}";
        $db->query( $sql, 'user' );
        
        $projs = array();
        while ( $proj = $db->assoc( 'user') ) {
            $projs[] = $proj;     
        }
        
        return array( 'projects' => $projs );
    }

    public function getPicture() {
    	for ( $i = 0; $i < 10; $i++ ) {
	    	if ( is_file( 'public/user_icon/' . $this->id . '_' . $i . '.pic') ) {
	            return '/public/user_icon/' . $this->id . '_' . $i . '.pic';
	        }
	    }

        return self::getDefaultPicture();
    }

    public static function getUserPicture( $id ) {
    	for ( $i = 0; $i < 10; $i++ ) {
	    	if ( is_file( 'public/user_icon/' . $id . '_' . $i . '.pic') ) {
	            return '/public/user_icon/' . $id . '_' . $i . '.pic';
	        }
	    }

        return self::getDefaultPicture();
    }

    public function getPictureNo() {
    	for ( $i = 0; $i < 10; $i++ ) {
	    	if ( is_file( 'public/user_icon/' . $this->id . '_' . $i . '.pic' ) ) {
	            return $i;
	        }
	    }

        return -1;
    }

    public static function getDefaultPicture() {
    	return '/public/user_icon/torso.png';
    }

    public function isSu() {
    	if ( $this->status == 2 ) {
    		return true;
    	}

    	return false;
    }
	
}
