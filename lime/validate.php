<?php

namespace lime;

class validate 
{
	public static $rules			= array();
	public static $messages			= array();
	public static $error			= null;

	public static function error($method, $key, $input, $check) {
		self::$error[rtrim($key,'[]')][] = array(
			'name'		=> $key,
			'data' 		=> $input,
			'method'	=> $method,
			'check'		=> $check,
			'message'	=> isset(self::$messages[$key][$method])
							? self::$messages[$key][$method] 
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
		foreach(self::$rules as $org_key => $value) {
			$key = rtrim($org_key, '[]');// javascript에서의 배열 네임과 php에서의 배열네임간의 차이 제거

			if(isset($data[$key]) && is_array($data[$key])) {
				$request_value = $data[$key];			
			} else if(isset($data[$key])) {
				$request_value = array(0=>$data[$key]);
			} else {//값이 안넘어옴
				$request_value = array(0=>'');
			}
			$input = isset($data[$key]) ? $data[$key] : ''; // checkbox 처리를 위해 배열을 허용하지 않으므로 name[]의 개개별 validate 검증은 불가능

			//foreach($request_value as $index => $input) {
				foreach($value as $method => $check) {
					if(false == \lime\validate\check::$method($input, $check)) {
						self::error($method, $org_key, $input, $check);
					}
				}
			//}
		}
		return self::$error;
	}

}

