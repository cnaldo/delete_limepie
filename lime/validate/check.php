<?php

namespace lime\validate;

class check
{
	public static $messages	= array(
		'maxlength'		=> "{0} 자 이내로 입력하세요.",
		'minlength'		=> "{0} 자 이상 입력하세요.",
		'rangelength'	=> "{0} ~ {1}자 길이의 문자를 입력하십시오.",
		'maxcount'		=> "{0} 개 이내로 선택하세요.",
		'mincount'		=> "{0} 개 이상 선택하세요.",
		'rangecount'	=> "{0} ~ {1} 개를 선택하세요.",
		'range'			=> "{0}, {1} 사이의 값을 입력하십시오.",
		'max'			=> "{0} 이하의 값을 입력하세요.",
		'min'			=> "{0} 이상의 값을 입력하세요.",
		'match'			=> "형식이 일치하지 않습니다.",
		'required'		=> "이 필드는 필수입니다.",
		'remote'		=> "이 필드를 수정하십시오.",
		'email'			=> "유효한 E-메일 주소를 입력하십시오.",
		'url'			=> "유효한 URL을 입력하십시오.",
		'equalTo'		=> "{0} 항목과 동일한 값을 입력하십시오.",
	);
	public static function required($data) {
		if(is_array($data) == true && count($data) > 0) {
			return true;
		} else if(strlen($data) > 0) {
			return true;
		}
		return false;
	}	
	public static function email($data) {
		$ex = "([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})";
		return preg_match("/^".($ex)."$/i",$data);
	}
	public static function url($data) {
		$ex = '(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})';
		return preg_match("/^".($ex)."$/i",$data);
	}
	public static function match($data, $match) {
		return preg_match("/^".$match."$/",$data);
	}
	public static function minlength($data, $minlength) {
		if(is_array($data) == false && mbstrlen($data) < $minlength
			|| is_array($data) == true && count($data) < $minlength
		){
			return false;
		} else {
			return true;
		}
	}
	public static function maxlength($data, $maxlength) {
		if(is_array($data) == false && mbstrlen($data) > $maxlength
			|| is_array($data) == true && count($data) > $maxlength
		){
			return false;
		} else {
			return true;
		}
	}
	public static function rangelength($data, $rangelength) {
		if(is_array($data) == false && (($a = mbstrlen($data)) < $rangelength[0] || $a > $rangelength[1])){
			return false;
		} else {
			return true;
		}
	}
	public static function mincount($data, $minlength) {
		if(is_array($data) == true && count($data) < $minlength){
			return false;
		} else {
			return true;
		}
	}
	public static function maxcount($data, $maxcount) {
		if(is_array($data) == true && count($data) > $maxcount){
			return false;
		} else {
			return true;
		}
	}
	public static function rangecount($data, $rangelength) {
		if(is_array($data) == true && (($a = count($data)) < $rangelength[0] || $a > $rangelength[1])){
			return false;
		} else {
			return true;
		}
	}	
	public static function max($data, $max) {
		if($data > $max){
			return false;
		} else {
			return true;
		}
	}
	public static function min($data, $min) {
		if($data < $min){
			return false;
		} else {
			return true;
		}
	}
	public static function range($data, $range) {
		if($data < $range[0] || $data > $range[1]){
			return false;
		} else {
			return true;
		}
	}
	public static function equalTo($data, $equalTo) {
		if($data != $equalTo){
			return false;
		} else {
			return true;
		}
	}
	public static function remote($data, $remote) {
		//	$test = $_GET['callback'];
		//	${$test} = create_function('$arg', 'return json_dec($arg);');

		$query_string = array(
			$org_key => _post($org_key)
		);
		if(isset($value['remote']['add'])) {
			foreach($value['remote']['add'] as $rk => $rv) {
				$query_string[$rv] = _post($rv);
			}
		}
		$context = stream_context_create(array('http' => array(
			'method'		=> 'POST',
			'header'		=> "Content-type: application/x-www-form-urlencoded\r\n",
			'content'		=> http_build_query($query_string)
		)));

		$tmp = json_dec(file_get_contents(HOST.$value['remote']['url'], false, $context), true);

		if(0 < (int)$tmp['result']['count']) {
			return false;
		} else {
			return true;
		}
	} 
}
