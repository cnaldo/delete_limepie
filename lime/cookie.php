<?php

namespace lime;

//header('P3P: CP="NOI CURa ADMa DEVa TAIa OUR DELa BUS IND PHY ONL UNI COM NAV INT DEM PRE"');

/**
 * crypt 클래스로 쿠키를 암호화, 출력이 시작된 이후에는 javascript cookie 활용
 *
 * @package       system\cookie
 * @category      system
 *
 * @param  string  $key   get
 * @param  mixed   $value set
 * @return string  $return 
 */

class cookie
{
	private static function _set($key, $value, $expire = 0, $path = "/", $domain = null, $secure = 0) {
		$key = self::_sethost($key);
		$domain = (true === is_null($domain) ? ini_get('session.cookie_domain') : $domain);

		if(strlen($value) > 4096) die('4 KB per cookie maximum');
		if($expire > 0 && $expire <= time()) $expire = time() + $expire;
        if (headers_sent()) {
			$_cook = 'document.cookie ="';
			$_cook .= $key.'="+escape("'.$value.'")+";';
			$_cook .= ($expire ? ' expires='.gmdate('D, d M Y H:i:s',$expire).' GMT;' : '');
			$_cook .= ($path   ? ' path='.$path.';' : '');
			$_cook .= ($domain ? ' domain='.$domain.';' : '');
			$_cook .= ($secure ? ' secure;' : '');
			$_cook .= '";';
		    echo '<script language="javascript">'.$_cook.'</script>';
			return true;
		}
		$_COOKIE[$key] = $_REQUEST[$key] = $value;
		return setcookie($key, $value, $expire, $path, $domain, $secure);
	}
    public static function set($key, $value, $expire = 0, $path = '/', $domain = null, $secure = 0) {
        $_value = \lime\crypt::pack($value);
		if( self::_set($key, $_value, $expire, $path, $domain, $secure)) {
			return $_value;
		} 
		return false;
    }
	private static function _get($key) {
		$key = self::_sethost($key);
		return true === isset($_COOKIE[$key]) ? $_COOKIE[$key] : false;
	}
	public static function get($key, $check = false) {
		return \lime\crypt::unpack(self::_get($key));
    }
	private static function _destroy($key, $value = '', $expire = 0, $path = '/', $domain = null, $secure = 0) {
		$key = self::_sethost($key);
		$domain = (true === is_null($domain) ? ini_get('session.cookie_domain') : $domain);

		if(isset($_COOKIE[$key])) {
			$_COOKIE[$key] = $_REQUEST[$key] = null;
			unset($_COOKIE[$key], $_REQUEST[$key]);

			if (headers_sent()) {
				$_cook = 'document.cookie ="';
				$_cook .= $key.'=;';
				$_cook .= ' expires=Thu, 01-Jan-70 00:00:01 GMT;';
				$_cook .= ($path ?   ' path='.$path.';' : '');
				$_cook .= ($domain ? ' domain='.$domain.';' : '');
				$_cook .= ($secure ? ' secure;' : '');
				$_cook .= '";';
				echo '<script language="javascript">'.$_cook.'</script>';
				return true;
			}
			
			return setcookie($key, '', time() - 3600, $path, $domain, $secure);;
		}
		return false;
	}
	public static function del($key, $value = '', $expire = 0, $path = '/', $domain = null, $secure = 0) {
		return self::_destroy($key, $value, $expire, $path, $domain, $secure);
	}
	private static function _sethost($key) {
		return str_replace('.','_',$_SERVER['HTTP_HOST']).'_'.$key;
	}
}
