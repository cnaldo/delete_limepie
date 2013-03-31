<?php

namespace lime\db;

class driver {
	static function __callStatic($func, $args) {
		$class = get_called_class();
		return call_user_func_array( array(\lime\db::getInstance($class), $func), $args);
	}
}
