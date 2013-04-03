<?php

class welcome_run extends apps_globals
{
	function index() {
		$this->set('msg','hello world');

		$this->contents('hi.tpl');
		return $this->display();		
	}

}
