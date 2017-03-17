<?phpclass githubView extends View {	protected $module = 'user';	protected function _prepare() {		$this->setModule();			global $User;		global $Config;		try {			if ( empty( $_SERVER["HTTPS"] ) || $_SERVER["HTTPS"] != 'on' ) {				$_SESSION['washttp'] = true;				Misc::Load( 'https://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );			}			$conf = $Config->getConfig( 'social' );			$github = new GithubApi( $conf['github']['key'], $conf['github']['secret'] );			$User->socialLogin( $github->getUserDetails() );						$unlock = '';			if ( !empty( $_SESSION['login_from'] ) ) {				$project = new Project( null, $_SESSION['login_from'] );				if ( $project->isEditable() ) $unlock = 'true';			}			if ( $_SESSION['washttp'] ) {				unset( $_SESSION['washttp'] );				Misc::Load( 'http://' . $_SERVER["HTTP_HOST"] . '/?mod=user&pg=githubhttpred&un=' . $unlock );			}			$Config->Templ->AddScript( 'inline_js', 'opener.phpfy.social.success(' . $unlock . '); opener.focus(); window.close();' );		} catch( Exception $e ) {			$Config->Templ->AddScript( 'inline_js', 'opener.phpfy.social.error("' . str_replace( ['"', "\n","\r"], "'", $e->getMessage() ) . '"); opener.focus(); window.close();' );		}    }};