<?php

date_default_timezone_set("ROK"); 

/*
ini_set('default_charset','utf-8');
ini_set('mbstring.internal_encoding','utf-8');
*/

if(in_array($_SERVER['HTTP_HOST'], array('apps.limepie.kr'))) {
	define('ENVIRONMENT', 'production');
} else if(in_array($_SERVER['HTTP_HOST'], array('test.limepie.kr'))) {
	define('ENVIRONMENT', 'testing');
} else {
	define('ENVIRONMENT', 'development');
}

if(ENVIRONMENT == 'production') {
	ini_set('display_errors', 'Off');
	ini_set('display_startup_errors', 'Off');
	ini_set('error_reporting', E_ALL);
	ini_set('log_errors', 'On');
} else {
	ini_set('display_errors', 'On');
	ini_set('display_startup_errors', 'On');
	ini_set('error_reporting', -1);
	ini_set('log_errors', 'On');
}

ini_set("session.cookie_lifetime", "0");
ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);

define('LANG', 'ko');

/* db class setting 
*/
class master	extends \lime\db\driver {}
class slave		extends \lime\db\driver {}
class master2	extends \lime\db\driver {}
