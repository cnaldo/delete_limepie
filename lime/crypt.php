<?php

namespace lime;

/**
 * 복호화 가능한 문자열로 암호화 
 *
 * @package	   system\encrypt
 * @category	  system
 */
/*

$s = array('a'=>'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');

\lime\crypt::set_key('encryptionke2y');
$e = \lime\crypt::pack($s);
$d = \lime\crypt::unpack($e);

*/
// lgorithm | mcrypt xor

class crypt
{
	public static $driver = 'mcrypt';
	public static function getDriver() {
		return '\\lime\\crypt\\'.self::$driver;
	}
	public static function set_key($key) {
		$driver = self::getDriver();
		return $driver::$key=$key;
	}
	public static function pack($plaintext, $key = false) {
		$driver = self::getDriver();
		return $driver::pack($plaintext, $key ? $key : $driver::$key);
	}
	public static function unpack($ciphertext, $key = false) {
		$driver = self::getDriver();
		return $driver::unpack($ciphertext, $key ? $key : $driver::$key);
	}
}

class CryptException extends \Exception 
{ 
}