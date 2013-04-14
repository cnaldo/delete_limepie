<?php

namespace lime;

class validate 
{
	public static $rules			= array();
	public static $messages			= array();
	public static $error			= null;

	public static function error($method, $org_key, $input, $check, $index = null) {
		$key = rtrim($org_key,'[]');

		if(is_null($index) === true) {
			$name = $key;
		} else {
			$name = $key.'['.$index.']';
		}
		self::$error[$key][] = array(
			'name'		=> $name,
			'data' 		=> $input,
			'method'	=> $method,
			'check'		=> $check,
			'message'	=> isset(self::$messages[$org_key][$method])
							? self::$messages[$org_key][$method] 
							: _t(\lime\validate\check::$messages[$method], $check)

		);
	}
	public static function run($rules, $data = array()) {

		self::$messages		= isset($rules['messages'])	? $rules['messages']	: array();
		self::$rules		= isset($rules['rules'])	? $rules['rules']		: $rules;

		if(0 == count($data)) {
			if(REQUEST_METHOD =='post') {
				$data = $_POST;
			} else {
				$data = $_GET;
			} 
		}

		$return = array();
		foreach(self::$rules as $org_key => $rule) {
			$key = rtrim($org_key, '[]');// javascript에서의 배열 네임과 php에서의 배열네임간의 차이 제거
			$is_array = false;
			if(isset($data[$key]) && is_array($data[$key])) {
				$is_array = true;
				$request_value = $data[$key];			
			} else if(isset($data[$key])) {
				$request_value = array(0=>$data[$key]);
			} else {//값이 안넘어옴
				$request_value = array(0=>'');
			}
			$ginput = isset($data[$key]) ? $data[$key] : '';

			foreach($request_value as $index => $input) {
				foreach($rule as $method => $check) {
					if(in_array($method, \lime\validate\check::$group_rules)) {
						if(false == \lime\validate\check::$method($ginput, $check)) {
							self::error($method, $org_key, $ginput, $check);
						}
					} else {
						if(!$input && (isset($rule['required']) == false || !$rule['required'])) continue; //값이 없고 필수도 아니면 패스. group function 일 경우 제외

						if(false == \lime\validate\check::$method($input, $check)) {
							self::error($method, $org_key, $input, $check, $is_array ? $index : null);
						}
					}
				} 
			}
		}
		return self::$error;
	}

}

