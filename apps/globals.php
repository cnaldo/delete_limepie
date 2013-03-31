<?php

class apps_globals extends \lime\controller 
{
	public $ver = '100';
	public function __construct() {
		parent::__construct();
		\lime\lang('main');	
	}
	protected final function display($ret = false) {
		$tpl = new \lime\tpl;
		$tpl->tpl_path		= APPS_FOLDER;
		$tpl->skin			= $this->seg('module').'/view/';
		$tpl->prefilter		= 'adjustPath & css,js,gif,jpg,jpeg,png,swf|fixHtml';
		$tpl->environment	= ENVIRONMENT;

		if(isset(\lime\bank()->template_define['layout']) == false && isset($this->layout) && $this->layout) {
			$this->layout($tpl->tpl_path.'layout/'.$this->layout);
		}
		$this->set('ver', $this->ver);
		$tpl->define(\lime\bank()->template_define);
		$tpl->assign(\lime\bank()->template);
		
		if(isset(\lime\bank()->template_define['layout']) == false) {
			return $tpl->display('contents', $ret);
		} else {
			return $tpl->display('layout', $ret);			
		}
	}
	protected final function set($arg = array(), $val = null) {
		$a = \lime\bank()->template;
		if (is_array($arg)) {
			\lime\bank()->template = array_mix ($a , $arg);
		} else {
			$p = @func_get_args();
			if(count($p)>1) {
				\lime\bank()->template = array_mix ($a, array($arg => $val));
			}
		}
	}
	protected final function get($attr, $key = null) {
		if(isset(\lime\bank()->template[$attr])) {
			if($key) {
				if(isset(\lime\bank()->template[$attr][$key])) {
					return \lime\bank()->template[$attr][$key];
				}
			} else {
				return \lime\bank()->template[$attr];
			}
		}
		return null;
	}
	protected final function define($arg = array(), $val = null) {
		$a = \lime\bank()->template_define;
		if (is_array($arg)) {
			\lime\bank()->template_define = array_mix ($a , $arg);
		} else {
			$p = @func_get_args();
			if(count($p)>1) {
				\lime\bank()->template_define = array_mix ($a, array($arg => $val));
			}
		}
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
