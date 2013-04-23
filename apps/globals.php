<?php


class apps_globals extends \lime\controller 
{
	public $ver = '100';
	public function __construct() {
		parent::__construct();
		\lime\Language::load('main');	
	}
	protected final function display($print = false) {
		$tpl = new \lime\tpl;
		$tpl->tpl_path		= APPS_FOLDER;
		$tpl->skin			= $this->getParameter('module').'/view/';
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
	protected final function set($arg = array(), $val = null) {
		return \lime\space::name('template')->setAttribute($arg, $val);
	}
	protected final function get($attr = null, $key = null) {
		return \lime\space::name('template')->getAttribute($attr, $key);
	}
	protected final function define($arg = array(), $val = null) {
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
	protected final function layout($arg = array(), $val = null) {
		return $this->_define('layout', $arg, $val);
	}
	protected final function contents($arg = array(), $val = null) {
		return $this->_define('contents', $arg, $val);
	}
	protected final function content($arg = array(), $val = null) {
		return $this->_define('content', $arg, $val);
	}
}
