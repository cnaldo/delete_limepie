<?php

namespace lime;

class error extends \lime\controller {
	public $info = array();
	/*
		// 클래스가 없음
		function class_not_exists($className) {}
		// 메소드가 없음
		function method_not_exists($methodName) {}
		// 폴더가 없음
		function folder_not_exists($folderName) {}
		// 파일이 없음
		function file_not_exists($fileName) {}
		
		// type : forward_loop ;; forward가 잘못설정되어 무한 루프에 빠짐
		function error($type) {}
	*/


	public function __call($func_name, $b) {
		pr($this->info);
	}

	public function js() {
		/* main/lang 이면 자바스크립트 호출이다. */
		if($this->raw(0) == 'main' && $this->raw(1) == 'lang') {
			echo 'var error = '.json_enc($this->info).';';
			return ;
		}	
	}

	public function php() {
	
	}
}