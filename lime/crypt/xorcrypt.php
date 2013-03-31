<?php

namespace lime\crypt;



class xorcrypt
{
	public static $key			= false;
	public static $compress		= true;

	private static function _bytexor($a,$b,$ilimit) { 
		$c=""; 
		for($i=0;$i<$ilimit;$i++) { 
			$c .= $a{$i}^$b{$i}; 
		} 
		return $c; 
	}
	public static function set_key($key) {
		self::$key = $key;
	}
	public static function pack($plaintext, $key = false) {		
		if (self::$key === false && $key == false) {
			throw new XorException('Please call set_key().');
		} 
		if(!$key && self::$key) {
			$key = self::$key;
		}
		$msg = json_encode($plaintext);

		if(self::$compress) {
			$msg	= gzcompress($msg);
		}	
		$string		= ""; 
		while($msg) { 
			$secureKey	=pack("H*",md5($key)); 
			$dec_limit	=strlen(substr($msg,0,16)); 
			$buffer		=self::_bytexor(substr($msg,0,16),$secureKey,$dec_limit); 
			$string		.=$buffer; 
			$msg		=substr($msg,16); 
		} 

		return base64_encode($string);
	}
	public static function unpack($xortext, $key = false) {
		if (self::$key === false && $key == false) {
			throw new XorException('Please call set_key().');
		} 
		if(!$key && self::$key) {
			$key = self::$key;
		}
		$msg = @base64_decode($xortext);
		if ($msg === false) {
			throw new McryptException('Invalid base-64 encoding.');
		}
		$msg_len	= strlen($msg); 
		$string		= ""; 
		while($msg) { 
			$secureKey	=pack("H*",md5($key)); 
			$buffer		=substr($msg,0,16); 
			$enc_limit	=strlen($buffer); 
			$string		.=self::_bytexor($buffer,$secureKey,$enc_limit); 
			$msg		=substr($msg,16); 
		} 
		if(self::$compress) {
			$string = @gzuncompress($string);
		}
		if($xortext && !$string) {
			throw new McryptException('decrypt error');
		}		
		$plaintext = json_decode($string, true);

		return $plaintext;
	}
}

class XorException extends \Exception 
{ 
}

