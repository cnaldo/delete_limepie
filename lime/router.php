<?php

namespace lime;
/*
	$router = new \lime\nrouter(array(
		//'(?P<module>admin|order)(?:/(?P<parameter>.*))?' => array(), 
		'(?P<module>[^/]+)?(?:/(?P<year>[^/]+))?(?:/(?P<parameter>.*))?' => array(
			//'basedir' => 'test'
		)
	));
	$router->setError('apps_error');
*/
class router {
	private $pathinfo;
	private $route		= array();
	private	$segment	= array();
	private	$parameter	= array();
	private $basedir	= 'apps';
	private $prefix		= '';
	private $module		= 'welcome';
	private $controller	= 'run';
	private $action		= 'index';
	private $error		= '\lime\error';
	private $matchRoute;
	private $systemVariables = array('basedir','module','action','controller','prefix'); // paramter로 받을수 없는 변수
	public	$prev;
	public	$is_error	= false;

	public function __construct($array = array()){
		$this->pathinfo	= $this->_getPathinfo();
		$this->segment	= explode('/',$this->pathinfo);
		$this->route	= $array;
	}
	public function setException($message = '', $method = 'error', $args = array()) {
		$this->is_error = true;
		$class			= $this->getError();
		$tmpObj			= new $class;
		$_args			= array(
			'method'	=> $method,
			'message'	=> $message
		);
		$tmpObj->info	= $_args + array('trace' => $args);
		if(is_callable(array(&$tmpObj, $method))) {
			return call_user_func_array(array(&$tmpObj, $method), $args);
		} else {
			throw new \Exception('error '.$method.' method_does_not_exist');
		}
	}
	public function addRoute($route = array()) {
		$this->route = $route;	
	}
	public function setBaseDir($basedir) {
		$this->basedir = $basedir;
	}
	public function getBaseDir() {
		return $this->basedir;
	}
	public function setPrefix($prefix) {
		$this->prefix = $prefix;
	}
	public function getPrefix() {
		return $this->prefix;
	}
	public function setModule($module) {
		$this->module = $module;
	}
	public function getModule() {
		return $this->module;
	}
	public function setController($controller) {
		$this->controller = $controller;
	}
	public function getController() {
		return $this->controller;
	}
	public function setAction($action) {
		$this->action = $action;
	}
	public function getAction() {
		return $this->action;
	}
	public function setError($error) {
		$this->error = $error;
	}
	public function getErrorController() {
		return $this->error;
	}
	private function _getPathinfo(){
		return (true === isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'],'/') : '');
	}
	public function getParameter($key=null) {
		if(true === is_null($key)) {
			return $this->parameter;
		}
		return true === isset($this->parameter[$key]) ? $this->parameter[$key] : null;
	}
	public function getSegment($key=false, $end = false) {
		if(false === $key) {
			return $this->segment;
		}
		if(true === $end) {
			return implode('/',array_slice ($this->segment, $key));      
		}
		return true === isset($this->segment[$key]) ? $this->segment[$key] : null;
	}
	private function setDefaultParameter() {
		$ret = array();
		foreach($this->systemVariables as $key) {
			$ret[$key] = $this->{'get'.$key}();
		}
		return $ret;
	}
	public function route() {
		if(false == is_array($this->route) || 0 == count($this->route)) {
			$this->route = array(
				'(?P<parameter>.*)' => array()
			);
		}
		foreach($this->route as $rule => $default) {
			if(preg_match('#^'.$rule.'$#', $this->pathinfo, $m1)) {
				$parameter = $this->setDefaultParameter();
				$this->parameter = $default + $parameter; // $default 우선

				$_path	= isset($m1['parameter']) && trim($m1['parameter']) != '' ? explode('/',rtrim($m1['parameter'], '/')) : array();
				for($i=0,$max=count($_path)+1;$i<=$max-2;$i+=2) { 
					if(true === isset($_path[$i]) && $_path[$i] && false == in_array($_path[$i], $this->systemVariables)) {
						$this->parameter[$_path[$i]] = (true === isset($_path[$i+1]) ? $_path[$i+1] : '');
					}
				}
				foreach($m1 as $key => $value) {
					if(false === is_numeric($key)) {
						$this->parameter[$key] = $value;
					}
				}
				$this->matchRoute = array($rule => $default);
				break;
			}	
		}
		return $this->parameter;
	}
}