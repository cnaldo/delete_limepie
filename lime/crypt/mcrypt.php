<?php

namespace lime\crypt;


class mcrypt
{
	public static $key			= false;
	public static $compress		= true;
	protected static $_cipher	= 'rijndael-256';
	protected static $_mode		= 'ofb';

	public static function set_key($key) {
		self::$key = $key;
	}
	public static function pack($plaintext, $key = false) {
		if (self::$key === false && $key == false) {
			throw new McryptException('Please call set_key().');
		} 
		if(!$key && self::$key) {
			$key = self::$key;
		}
		$plaintext = json_encode($plaintext);
		if(self::$compress) {
			$plaintext = gzcompress($plaintext);
		}	
		$key		= substr(hash('sha256', $key), 0, mcrypt_get_key_size(self::$_cipher, self::$_mode));// Resize the key.
		$iv			= mcrypt_create_iv(mcrypt_get_iv_size(self::$_cipher, self::$_mode), MCRYPT_DEV_URANDOM);// Create an IV.
		$ciphertext	= $iv . mcrypt_encrypt(self::$_cipher, $key, $plaintext, self::$_mode, $iv);// Encrypt, and attach the IV to the ciphertext.
		
		return base64_encode($ciphertext);
	}
	public static function unpack($ciphertext, $key = false) {
		if (self::$key === false && $key == false) {
			throw new McryptException('Please call set_key().');
		} 
		if(!$key && self::$key) {
			$key = self::$key;
		}
		$ciphertext = @base64_decode($ciphertext);
		if ($ciphertext === false) {
			throw new McryptException('Invalid base-64 encoding.');
		}
		$ivsize		= mcrypt_get_iv_size(self::$_cipher, self::$_mode);// Detach the IV from the ciphertext.
		$iv			= substr($ciphertext, 0, $ivsize);
		$key		= substr(hash('sha256', $key), 0, mcrypt_get_key_size(self::$_cipher, self::$_mode));// Resize the key.
		$plaintext	= mcrypt_decrypt(self::$_cipher, $key, substr($ciphertext, $ivsize), self::$_mode, $iv);// Decrypt.
			
		if(self::$compress) {
			$plaintext2 = @gzuncompress($plaintext);
		} else {
			$plaintext2 = $plaintext;
		}
		if($plaintext && !$plaintext2) {
			throw new McryptException('decrypt error');
		}
		$plaintext = json_decode($plaintext2, true);

		return $plaintext; //rtrim($plaintext, "\0");
	}
}

class McryptException extends \Exception 
{ 
}