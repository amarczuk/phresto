<?php

	require_once( __DIR__ . '/../../vendor/autoload.php' );

	function FRautoload($class_name)
	{
		global $CurrentModule;
	
		$plik = Rejestr::sprawdz( $class_name, $CurrentModule );
		
		if ( $plik ) {
			require_once $plik;
		} else {
			Rejestr::generuj();
			$plik = Rejestr::sprawdz( $class_name, $CurrentModule );
			
			if ( $plik ) {
				require_once $plik;
			} else {
				echo 'unnown class ' . $class_name;
				die();
			}
		}
	}

	spl_autoload_register( 'FRautoload' );

	function getGlobal($nazwa)
	{
	
		global $$nazwa;
		return $$nazwa;
	
	}
	
	
	function setGlobal($nazwa, $var)
	{
	
		global $$nazwa;
		$$nazwa = $var;
	
	}
	