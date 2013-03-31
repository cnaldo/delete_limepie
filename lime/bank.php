<?php

namespace lime;

/**
 * 싱글톤 패턴, 매직함수를 사용하여 키,값의 저장소로 사용
 *
 * @package       system\bank
 * @category      system
 */
 
function bank($chk=null) {
	if($chk == null) {
		return bank::data();
	} else {
		// Fatal error: Can't use function return value in write context 
		// get 만 가능
		return bank::data()->$chk; 
	}
}

class _bank 
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
	/* set variables */
	public function set($arg = array(), $val = null) {
		$a = $this->_variables;
		if (is_array($arg)) {
			$this->_variables = array_mix ($arg, $a);
		} else {
			$p = @func_get_args();

			if(count($p)>1) {
				$this->_variables = array_mix (array($arg => $val), $a);
			}
		}
	}
	/* reset variables */
	public function reset() {
		self::$_instance->_variables = null;
		unset(self::$_instance->_variables);
		//self::$_instance->_variables = array();
	}
	/* destroy a variable */
	public function __destruct() {
		bank()->reset();
	}
}

class bank extends _bank
{
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
}
