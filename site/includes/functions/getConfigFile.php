<?php

function getIniProp($prop)
{
	$value = '';
	if(substr(php_sapi_name(), 0, 3) == 'cli') {
		$ini = parse_ini_file('/var/www/www-app/frc_scout/includes/config.ini');
	}		
	elseif(substr(PHP_SAPI, 0, 6) == 'apache') {
		$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/site/includes/config.ini');
	}
	if(isset($ini[$prop]))
	{
		$value = $ini[$prop];
	}
	error_log($prop.' -- '.$value, 0);
	return $value;
}






?>