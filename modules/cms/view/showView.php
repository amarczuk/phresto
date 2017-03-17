<?php

class showView extends MixedView {

	protected $module = 'cms';

	protected function _prepare() {
	
		global $User;
		global $Config;
		$this->setModule();

		$cms = new Cms( null, [ 'url' => $_GET['id'] ] );
		if ( empty( $cms->id ) ) {
			Misc::Go( '/404' );
		}

		$cat = new CmsCategory( $cms->categoryid );
		$arts = $cms->getFromCategory( $cat->id, !$User->isSu() );
		$tmpl = $this->isAjax ? 'art' : 'fullart';

		$this->config->Templ->Add( $tmpl, array_merge( $cms->_properties, [ 'catName' => $cat->title,
																			    'arts' => $arts,  
																			    'su' => $User->isSu() ? 1 : 0, 
																			    'caturl' => $cat->url ] ) );
		if ( $this->isAjax ) {
			$this->config->Templ->AddScript( 'inline_js', '$( ".mixedlink" ).addClass( "ajaxbutton" );' );
		} else {
			$Config->Templ->AddScript( 'phpfy.js', '', 'main' );
	        $Config->Templ->AddScript( 'highlight.pack.js', '', 'code' );
	        $Config->Templ->AddCSS( 'foundation-icons/foundation-icons.css', '', 'kernel' );
	        $Config->Templ->AddCSS( 'devicon/devicon.css', '', 'kernel' );
	        $Config->Templ->AddCSS( 'main.css', '', 'main' );
			$Config->Templ->AddCSS( 'highlight/solarized_light.css', '', 'code' );
		}
	}
}
  
