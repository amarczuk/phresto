<?php

namespace Phresto;

class ViewOld {
	
	protected $module = 'kernel';
	protected $config;
	
	public function __construct() {
		global $Config;
		
		ob_start();
		
		$this->config = $Config;
		
		$this->config->Templ->lang = $this->config->Misc->Lang();
		
		$this->config->Templ->icon = 'kernel/img/favicon.ico';
		$this->config->Templ->headers['author'] = 'Adam Marczuk [www.marczuk.info]';
		$this->config->Templ->headers['viewport'] = 'width=device-width, initial-scale=1.0';
		$this->config->Templ->title = HEAD_TYTUL;
		
		$this->config->Templ->headers['description'] = HEAD_DESC;
		$this->config->Templ->headers['keywords'] = HEAD_KEYW;
		
		$this->config->Templ->AddScript('jquery.js');
		//$this->config->Templ->AddScript('jquery.touche.min.js');
		$this->config->Templ->AddScript('jquery.google-analytics.js');
        $this->config->Templ->AddScript('vendor/modernizr.js');
        $this->config->Templ->AddScript('vendor/jquery.cookie.js');
		$this->config->Templ->AddScript('foundation.min.js');
		$this->config->Templ->AddScript('ckeditor/ckeditor.js');

        $this->config->Templ->AddCSS('fonts/Khand/stylesheet.css');
        $this->config->Templ->AddCSS('foundation.css');   
        $this->config->Templ->AddCSS('js/ckeditor/skins/moono/editor.css', 'kernel', true );   
        $this->config->Templ->AddCSS('default.css');   
        

	}

	public function Run() {
		$this->_prepare();
	}
	
	
	protected function _prepare() {
		global $Config;
		$this->setModule();
		$this->config->Templ->AddCSS('style-fnd.css');

        $Config->Templ->AddScript( 'phpfy.js', '', 'main' );
        $Config->Templ->AddCSS( 'foundation-icons/foundation-icons.css' );
        $Config->Templ->AddCSS( 'devicon/devicon.css' );
        $Config->Templ->AddCSS( 'main.css', '', 'main' );

		$this->config->Templ->Add( 'e404', array() );
	}
	
	protected function setModule() {
		$this->config->CurrentModule = $this->module;
	}
	
	public function __destruct() {
	
		if ( !$this->config->Templ->non_header ) $this->config->Templ->AddScript( 'inline_js', '$.trackPage("UA-11307094-3");' );
		echo $this->config->Templ->Get();

		$this->config->Misc->zapiszCzas();
		$this->config->maindb->close();
	
		ob_end_flush();
	}
	
}