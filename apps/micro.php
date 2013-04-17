<?php

class apps_micro extends \lime\micro 
{
	public $ver = '100';
	public $layout = 'blue.tpl';
	public function __construct() {
		parent::__construct();
		\lime\Language::load('main');	
	}
	public function display($print = false) {
		$tpl = new \lime\tpl;
		$tpl->tpl_path		= APPS_FOLDER;
		$tpl->skin			= 'order/view/';
		$tpl->prefilter		= 'adjustPath & css,js,gif,jpg,jpeg,png,swf|fixHtml';
		$tpl->environment	= ENVIRONMENT;

		if(isset(\lime\space::data()->template_define['layout']) == false && isset($this->layout) && $this->layout) {
			$this->layout($tpl->tpl_path.'layout/'.$this->layout);
		}
		$this->set('ver', $this->ver);
		$tpl->define(\lime\space::name('template_define')->getAttributes());
		$tpl->assign(\lime\space::name('template')->getAttributes());
		
		if(isset(\lime\space::data()->template_define['layout']) == false) {
			return $tpl->display('contents', $print);
		} else {
			return $tpl->display('layout', $print);			
		}
	}
	public function set($arg = array(), $val = null) {
		return \lime\space::name('template')->setAttribute($arg, $val);
	}
	public function get($attr = null, $key = null) {
		return \lime\space::name('template')->getAttribute($attr, $key);
	}
	public function define($arg = array(), $val = null) {
		return \lime\space::name('template_define')->setAttribute($arg, $val);
	}
	private function _define($name, $arg = array(), $val = null) {
		if(is_array($arg) == false) {
			$arg = array(
				$name => $arg	
			);
		}
		return $this->define($arg, $val);
	}
	public function layout($arg = array(), $val = null) {
		return $this->_define('layout', $arg, $val);
	}
	public function contents($arg = array(), $val = null) {
		return $this->_define('contents', $arg, $val);
	}
	public function content($arg = array(), $val = null) {
		return $this->_define('content', $arg, $val);
	}
}
