<?php

$i = 0;
define('PARAM_BOOL',		1 << $i++);
define('PARAM_INT',			1 << $i++);
define('PARAM_STR',			1 << $i++);
define('PARAM_BASE64',		1 << $i++);
define('PARAM_ARRAY',		1 << $i++);
define('PARAM_SERIALIZE',	1 << $i++);
define('PARAM_JSON',		1 << $i++);
define('PARAM_SAFE',		1 << $i++); //URL에 허용되는 안전한 문자열 a-zA-Z0-9\-_\.
define('PARAM_URL',			1 << $i++);
define('PARAM_EMAIL',		1 << $i++);

class param  
{
	public static function get(&$value, $default = false, $type = 0) {

		if ($type & PARAM_SAFE) {
			$value = preg_replace('/[^a-zA-Z0-9\-_\.]/','',$value);
		}

		if (is_array($type)) { // array 에 포함되어있는지 검사
			return false === empty($value) && in_array($value, $type) ? $value : $default;
		} else if ($type & PARAM_EMAIL) {
			$ex = "([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})";
			return preg_match("/^".($ex)."$/i",$value) ? $value : $default;
		} else if ($type & PARAM_URL) {
			$ex = '(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})';
			return preg_match("/^".($ex)."$/i",$value) ? $value : $default;
		} else if ($type & PARAM_BOOL) {
			return false === empty($value) ? (bool)$value : (bool)$default;
		} else if ($type & PARAM_INT) {
			return false === empty($value) ? (float)preg_replace('/[^0-9\.\-]/','',$value) : (integer)$default;
		} else if ($type & PARAM_STR) {
			return false === empty($value) ? (string)trim($value) : (string)$default;
		} else if ($type & PARAM_BASE64) {
			if(false === empty($value) && $data=base64_decode($value) ) {
				return $data;
			} 
			return $default;
		} else if ($type & PARAM_ARRAY) {
			if( false === empty($value) ) {
				return is_array($value) == true ? (array)$value : array($value);
			} else {
				return (array)$default;
			}
		} else if ($type & PARAM_SERIALIZE) {
			return false === empty($value) ? unserialize($value) : $default;
		} else if ($type & PARAM_JSON) {
			return false === empty($value) ? json_decode($value) : $default;
		} else if (true === is_string($value) || !trim($value)) {
			return false === empty($value) ? trim($value) : $default;
		} else {
			return $value;
		}
	}
}

function _post($key, $default = false, $type = 0) {
	return $_POST[$key]		= param::get($_POST[$key], $default, $type);
}
function _cook($key, $default = false, $type = 0) {
	return $_COOKIE[$key]	= param::get($_COOKIE[$key], $default, $type);
}
function _sess($key, $default = false, $type = 0) {
	return $_SESSION[$key]	= param::get($_SESSION[$key], $default, $type);
}
function _files($key, $default = false, $type = 0) {
	return $_FILES[$key];
}
function _get($key, $default = false, $type = 0) {
	return $_GET[$key]		= param::get($_GET[$key], $default, $type);
}
function _request($key, $default = false, $type = 0) {
	return $_REQUEST[$key]	= param::get($_REQUEST[$key], $default, $type);
}
function _link(){
	$q = array(); $tmp = array();
	parse_str($_SERVER['QUERY_STRING'], $q);

	$args = func_get_args();
	foreach($args as $key => $value) {
		list($k, $v) = explode('=', $value);
		if($k && $v) {
			$tmp[] = $k.'='.$v;
		}
		unset($q[$k]);
	}
	foreach ($q as $k=>$v) {
		if($k && $v) {
			$tmp[] = $k.'='.$v;
		}
	}
	return implode('&amp;', $tmp);
}