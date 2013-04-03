<?php

namespace lime;

class framework
{
	private static $instance = null;
	public $route;
	public function __construct() {}
	public function __destruct() {}
	public static function getInstance() {       
		if (null === self::$instance) {      
			self::$instance = new \lime\framework;      
		}   
		return self::$instance;  
	}
	public function setRouter($route) {
		$route->route();
		$this->route = $route;
	}
	private function action($args = null) {
		$module			= str_replace('/',DS,$this->route->query['module']);
		$controller		= str_replace('/',DS,$this->route->query['controller']);
		$action			= str_replace('/',DS,$this->route->query['action']);
		$basedir		= str_replace('/',DS,$this->route->query['basedir']);
		$prefix			= str_replace('/',DS,$this->route->query['prefix']);
	 	$className		= ($module ? $module.'_':'').str_replace(DS,'_',$controller);
		$errorClassName	= $this->route->defaultError;
		$moduleBasedir	= HTDOCS_FOLDER.($basedir ? $basedir.DS : '')
						.($prefix ? str_replace('_',DS,$prefix).DS:'')
						.($module ? str_replace('_',DS,$module).DS : '');;
		$folderName		= $moduleBasedir.$controller;
		$fileName		= $folderName.'.php';
		$folderName		= preg_replace('#'.preg_quote(DS,'#').'([^'.preg_quote(DS,'#').']+).php$#','',$fileName);
		$_args = array(
			'folder'	=> $folderName,
			'file'		=> $fileName,
			'class'		=> $className,
			'method'	=> $action
		);
		if(($class_exist = class_exists($className, false)) /* 로드됨 */ || ($file_exist = is_file($fileName)) /* 파일이 있음 */) {
			if(isset($file_exist) && $file_exist) {
				require_once($fileName);
				if (!($class_exist = class_exists($className, false))) {
					return $this->route->setException(\lime\_('클레스 없음'), 'class_does_not_exist', $_args);
				}
			}
			$insObj = new $className;
			if(method_exists($insObj, '__init')) {
				$tmp = $insObj->__init();	
				if(is_null($tmp) === false) {
					return $tmp;
				}
			}
			if(($method = (true === method_exists($insObj, REQUEST_METHOD.'_'.$action)) && is_callable(array(&$insObj, REQUEST_METHOD.'_'.$action)) ) 
				|| (true === method_exists($insObj, $action) && is_callable(array(&$insObj, $action)) )
			) { /* request type을 메소드 명에 붙여 하나의 url로 두가지 역할 할수 있음 */
				if(isset($method) === true && $method === true) {
					$action = REQUEST_METHOD.'_'.$action;
				}
				return call_user_func_array(array(&$insObj, $action), $args ? (is_array($args) ? $args : array($args)) : $this->route->getSegment());
			} else {
				return $this->route->setException(\lime\_('메소드 없음'), 'method_does_not_exist', $_args);
			}
		} else if(!is_dir($folderName)) {
			return $this->route->setException(\lime\_('폴더 없음'), 'folder_does_not_exist', $_args);
		} else {
			return $this->route->setException(\lime\_('파일 없음'), 'file_does_not_exist', $_args);
		}
	}
	public function forward($config) {
		return $ret = $this->_forward($config['route'], (isset($config['args']) ? $config['args'] : array()));
	}
	private function _forward($array = array()) {
		$prev_route			= $this->route;
		$router				= new \lime\router($array);
		$router->setError($this->route->defaultError);

		$front				= framework::getInstance();
		$front->setRouter($router);

		$new_route			= $front->route;
		$new_route->prev	= $prev_route;

		if($prev_route->matchRoute == $new_route->matchRoute) {
			return $this->route->setException(\lime\_('같은 곳으로 포워딩 되고 있습니다.'), 'forward_loop', array());
		}
		return $front->dispatch();
	}
	public function dispatch($args = null) {
		$ret = null;
		if($config = $this->action($args)) {
			if(is_array($config) === false && is_null($config) === false) {
			}
			$url = is(@$config['action'], @$config['path'], @$config['url']);
			if(is_array($config) === true && isset($config['type']) === true) {
				switch($config['type']) {
					case 'template' :
						break;
					case 'forward' : // forward to another action
						$ret = $this->_forward($config['route'], (isset($config['args']) ? $config['args'] : array()));
						break;
					case 'redirect' : // redirect to other location
						redirect($url, @$config['msg']);
						break;
					case 'submit' :
						submit($url, $config);
						break;
					case 'mail' :
						break;
					default :
						echo 'default';
				}
			}  else {
				$ret = $config;
			}
		}  else {
		}
		return $ret;
	}
}
