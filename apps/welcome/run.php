<?php

class welcome_run extends apps_globals
{
	function index() {
/*
		$s = array(
			'a'=>'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'
		);
		try {
			$key = '';//a';
			$e =  \lime\crypt::pack($s, $key);
			$d =  \lime\crypt::unpack($e, $key);
			print_r($e);
			print_r($d);
		} catch(Exception $e) {
			echo 'crypt err';
		}		
*/
		//echo 'hello world';
		$this->contents('hi.tpl');
		return $this->display(true);		
	}

}