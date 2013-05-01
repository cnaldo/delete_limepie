<?php

/* configure start */
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);

// System Start Time
define('START_TIME', microtime(true));

// System Start Memory
define('START_MEMORY_USAGE', memory_get_usage());

// Extension of all PHP files
define('EXT', '.php');

// Absolute path to the system folder
define('SP', realpath(__DIR__). DS);

// Is this an AJAX request?
define('AJAX_REQUEST', strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');

// The current TLD address, scheme, and port
define('DOMAIN', (strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'
	. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : ''));

/**
 * 클래스 자동 인클루드
 * __autoload를 사용하여 className의 _를 /로 변경하여 자동으로 인클루드
 */
function __autoload($className) {
	if( true != @include str_replace(array('_','\\'),DS,(strtolower($className))).'.php') {
		//if(true == in_array($className, array( 'lime\\master', 'lime\\slave', 'lime\\master'))) {
		//	throw new \Exception('master 앞에 \\를 붙이세요');			
		//} else {
		//	throw new \Exception('include error입니다. 혹시 namespace scope를 위반한것이 아닌지 확인하세요. : '.$className);	
		//}
	}
}

define('HTDOCS_FOLDER', realpath(dirname(__file__).'/..').DS);

$_SERVER['HTDOCS_FOLDER'] = HTDOCS_FOLDER;

//define('NL', "\n"); 

/* folder set */
define('DOCUMENT_FOLDER', rtrim($_SERVER['DOCUMENT_ROOT'], DS).DS);
define('ROOT_FOLDER'	, realpath(HTDOCS_FOLDER.'..').DS);
define('SYSTEM_FOLDER'	, realpath(HTDOCS_FOLDER.'lime').DS);
define('APPS_FOLDER'	, HTDOCS_FOLDER.'apps'.DS);
define('DATA_FOLDER'	, HTDOCS_FOLDER.'files'.DS);
define('VENDOR_FOLDER'	, HTDOCS_FOLDER.'vendor'.DS.'php'.DS);
define('SOURCE_FOLDER'	, VENDOR_FOLDER.'source'.DS);
define('PEAR_FOLDER'	, SOURCE_FOLDER.'pear'.DS);
define('CONFIG_FOLDER'	, HTDOCS_FOLDER.'config'.DS);
define('LAYOUT_FOLDER'	, HTDOCS_FOLDER.'layout'.DS);
define('WIDGET_FOLDER'	, HTDOCS_FOLDER.'widget'.DS);

/* url set */

define('BASE_PATH'		, ($a = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/')) ? $a.'/' : '/');
define('ADD_PATH'		, '/'.($a = rtrim(str_replace(DOCUMENT_FOLDER,'',HTDOCS_FOLDER),'/')).($a ? '/' : ''));
define('ADD_DATA_PATH'	, '/'.($a = rtrim(str_replace(DOCUMENT_FOLDER,'',DATA_FOLDER),'/')).($a ? '/' :''));

set_include_path(
	HTDOCS_FOLDER
	.PS.SYSTEM_FOLDER
	.PS.VENDOR_FOLDER
	.PS.SOURCE_FOLDER
	.PS.APPS_FOLDER
	.PS.'.'
	.PS.PEAR_FOLDER
);

if (!defined('PHP_EOL')) define('PHP_EOL', strtoupper(substr(PHP_OS,0,3) == 'WIN') ? "\r\n" : "\n");

define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
					&& !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
					&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

define('IS_POST', isset($_SERVER["REQUEST_METHOD"]) 
					&& !empty($_SERVER["REQUEST_METHOD"]) 
					&& strtolower($_SERVER["REQUEST_METHOD"]) == 'post');

define('REQUEST_METHOD', (strtolower($_SERVER["REQUEST_METHOD"] ? $_SERVER["REQUEST_METHOD"] : 'get')));

function getProtocal() {
	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? 'https://' : 'http://';
}
function getHost() {
	return preg_replace("/^www\./","",$_SERVER['HTTP_HOST']);//.$_SERVER['REQUEST_URI'];
}
function getProtocalHost() {
	return getProtocal().getHost();
}
function getUrl() {
	return getProtocal().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

define('HOST',		getProtocalHost());
define('URI',		getUrl());

function _t($msgid, $arr = null) {
	return \lime\_($msgid, $arr);
}
function __t($module, $msgid, $arr = null) {
	return \lime\__($module, $msgid, $arr);
}

/*필수파일 인클루드*/

require_once("lime/function.php");
require_once("lime/clearstatcache.php");
require_once("lime/language.php");

//if(file_exists("config/construct.php")) {
	require_once("config/construct.php");
//}

