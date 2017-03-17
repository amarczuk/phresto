<?php

class showCatView extends MixedView {

	protected $module = 'cms';

	protected function _prepare() {
	
		global $User;
		global $Config;
		$this->setModule();

		$cat = new CmsCategory( null, [ 'url' => $_GET['id'] ] );
		if ( empty( $cat->id ) ) {
			Misc::Go( '/404' );
		}

		$cms = new Cms();
		$arts = $cms->getFromCategory( $cat->id );

		if ( count( $arts ) == 1 ) {
			Misc::Go( '/cms/' . $arts[0]['url'] );
		}
	
		$tmpl = $this->isAjax ? 'cat' : 'fullcat';
		$this->config->Templ->Add( $tmpl, array_merge( $cat->_properties, [ 'arts' => $arts, 'cmsPageTmpl' => 'cat', 'su' => ( $User->isSu() ) ? '1' : '0' ] ) );

		if ( $this->isAjax ) {
			$this->config->Templ->AddScript( 'inline_js', '$( ".mixedlink" ).addClass( "ajaxbutton" );' );
		} else {
			$Config->Templ->AddScript( 'phpfy.js', '', 'main' );
	        $Config->Templ->AddCSS( 'foundation-icons/foundation-icons.css', '', 'kernel' );
	        $Config->Templ->AddCSS( 'devicon/devicon.css', '', 'kernel' );
	        $Config->Templ->AddCSS( 'main.css', '', 'main' );
		}
	}
}
  
