<?php

class apps_error extends apps_globals {
	public $layout = 'error.tpl';
	public $info = array();
	
	/*
		// 클래스가 없음
		function class_does_not_exist($className) {}
		// 메소드가 없음
		function method_does_not_exist($methodName) {}
		// 폴더가 없음
		function folder_does_not_exist($folderName) {}
		// 파일이 없음
		function file_does_not_exist($fileName) {}
		
		// type : forward_loop ;; forward가 잘못설정되어 무한 루프에 빠짐
		function error($type) {}
	*/

	public function method_does_not_exist($methodName) {
		$this->set(array(
			'info' => $this->info
		));

		return $this->display(true);
	}
	public function folder_does_not_exist($folderName) {
		$this->set(array(
			'info' => $this->info
		));

		return $this->display(true);
	}
	public function class_does_not_exist($className) {
		$this->set(array(
			'info' => $this->info
		));

		return $this->display(true);
	}
	public function file_does_not_exist($fileName) {
		$this->set(array(
			'info' => $this->info
		));

		return $this->display(true);
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