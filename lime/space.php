<?php

namespace lime;

/**
 * 싱글톤 패턴, 매직함수를 사용하여 키,값의 저장소로 사용
 *
 * @package       system\space
 * @category      system
 */
 
class _space
{
	public $_variables = array();
	static protected $_instance;
	
	public function __construct() {
	}
    public function __get($key) {
    	return isset($this->_variables[$key]) ? $this->_variables[$key] : array(); 
    }
    public function __isset($key) {
		return isset($this->_variables[$key]);
    }
	public function __set($key, $val) {
		return $this->_variables[$key] = $val;
	}
	public function __unset($key) {
		if ( isset($this->_variables[$key])) {
			unset($this->_variables[$key]);
		}
	}
	/* destroy a variable */
	public function __destruct() {
 		self::$_instance->_variables = null;
		unset(self::$_instance->_variables);
	}
}

class space extends _space
{
	public static $name = 'globals';
    public static function data() {
        if (null === parent::$_instance) {
            parent::$_instance = new self();
        }
        return parent::$_instance;
    }
    public static function lang($temp=null) {
        if (null === parent::$_instance) {
            parent::$_instance = new self();
        }
        return parent::$_instance;
    }
    public static function model($temp=null) {
        if (null === parent::$_instance) {
            parent::$_instance = new self();
        }
        return parent::$_instance;
    }
    public static function name($name) {
    	self::$name = $name;
    	return self::data();
    }  
    public static function setAttribute($arg = array(), $val = null) {
		$a = self::data()->{self::$name};
		if (is_array($arg)) {
			self::data()->{self::$name} = array_mix ($a , $arg);
			return $arg;
		} else {
			$p = @func_get_args();
			if(count($p)>1) {
				self::data()->{self::$name} = array_mix ($a, array($arg => $val));
				return $val;
			}
		}
    }
	public static function getAttributes($name=null) {
		if($name) {
			return self::data()->{$name};
		} else {
			return self::data()->{self::$name};
		}
	}
	public static function getAttribute($attr = null, $key = null) {
		if($attr == null) {
			return self::data()->{self::$name};
		} else if(isset(self::data()->{self::$name}[$attr])) {
			if($key) {
				if(isset(self::data()->{self::$name}[$attr][$key])) {
					return self::data()->{self::$name}[$attr][$key];
				}
			} else {
				return self::data()->{self::$name}[$attr];
			}
		}
		return null;
	}
}

