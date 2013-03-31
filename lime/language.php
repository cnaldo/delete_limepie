<?php

namespace lime;

/* load language */
function lang($module) {
	return Language::load($module);
}

/* _translations */
function _($msgid, $arr = null) {
	return Language::_($msgid, $arr);
}

/* module _translations */
function __($module, $msgid, $arr = null) {
	return Language::__($msgid, $arr);
}

class Language {
	public static $_translations;

	public static function load($module, $lang = NULL) {
		if(isset(self::$_translations['LANGUAGE'][$module]) == false) {
			$map = self::getLangMap($module, $lang);
			self::$_translations['LANGUAGE'][$module] = $map;

			$a = isset(self::$_translations['LANGUAGES']) ? self::$_translations['LANGUAGES'] : array();
			self::$_translations['LANGUAGES'] = array_mix ($a, self::$_translations['LANGUAGE'][$module]);
		}
		return self::$_translations['LANGUAGE'][$module];		
	}
	public static function add($module, $language) {
		return self::$_translations['LANGUAGE'][$module] = $language;		
	}	
	public static function _($msgid, $arr = null) {
		$lang = self::$_translations['LANGUAGES'];
		if(isset($lang[$msgid])) {
			$str = $lang[$msgid];
		} else {
			$str = $msgid;
		}
		if(is_array($arr)) { /*두번째 param이 배열일 경우 sprintf*/
			$str = self::valid_sprintf($str, $arr);
		}
		return $str;
	}
	public static function __($module, $msgid, $arr = null) {
		$lang = isset(self::$_translations['LANGUAGE'][$module]) ? self::$_translations['LANGUAGE'][$module] : array();
		if(isset($lang[$msgid])) {
			$str = $lang[$msgid];
		} else {
			$str = $msgid;
		}
		if(is_array($arr)) { /*두번째 param이 배열일 경우 sprintf*/
			$str = self::valid_sprintf($str, $arr);
		}
		return $str;
	}
	private static function valid_sprintf($format, $array) {
		$format = preg_replace("/\{([0-9]+)\}/","%s",$format);
		return call_user_func_array('sprintf', array_merge(array($format) , $array));
	}	
	private static function getLangMap($module, $lang=null) {
		if ($lang) {
		} else {
			$lang = LANG;
		}
		$filename = APPS_FOLDER .$module. '/lang/' . $lang . '.php';
		
		if (\lime\file_exists($filename)) {
			include $filename;
			if (isset($translations) && is_array($translations)) {
				return $translations;
			} else {
				return array();
				// throw new LanguageException($filename . ' does not contain $translations.');
			}
		} else {
			return array();
			// throw new LanguageException($filename . ' does not exist.');
		}
	}
}

class LanguageException extends \Exception { }
