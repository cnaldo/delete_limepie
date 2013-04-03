<?php

namespace lime;

class router
{
	public $defaultModule		= 'main';
	public $defaultController	= 'index';
	public $defaultAction		= 'index';
	public $defaultBasedir		= '';
	public $defaultPrefix		= '';
	public $defaultError		= 'lime\error';
	public $query;
	public $segment;
	public $route;
	public $matchRoute;
	public $prev;
	public $is_error = false;
	private $pathinfo; 
	public function __construct($array = array()){
		$this->pathinfo	= $this->_getPathinfo();
		$this->segment		= explode('/',$this->pathinfo);
		$this->route	= $array;
	}
	public function setException($message = '', $method = 'error', $args = array()) {
		$this->is_error = true;
		$class	= $this->defaultError;
		$tmpObj	= new $class;
		$_args	= array(
			'method'	=> $method,
			'message'	=> $message
		);
		$tmpObj->info = $_args + array('trace' => $args);
		if(is_callable(array(&$tmpObj, $method))) {
			return call_user_func_array(array(&$tmpObj, $method), $args);
		} else {
			throw new \Exception('error '.$method.' method_does_not_exist');
		}
	}
	public function addRouter($array = array()) {
		$this->route			+= $array;
	}
	public function setBasedir($forderName) {
		$this->defaultBasedir	= $forderName;
	}
	public function setError($controllerName) {
		$this->defaultError	= $controllerName;
	}
	public function setPrefix($forderName) {
		$this->defaultPrefix	= $forderName;
	}
	/* default module */
	public function setModule($moduleName) {
		$this->defaultModule	= $moduleName;
	}
	public function getModule() {
		return $this->defaultModule;
	}
	/* default action */
	public function setAction($actionName) {
		$this->defaultAction	= $actionName;
	}
	/* default controller */
	public function setController($controllName) {
		$this->defaultController= $controllName;
	}
	/* get matched param */
	public function getQuery($key=false) {
		if(false === $key) {
			return $this->query;
		}
		return true === isset($this->query[$key]) ? $this->query[$key] : null;
	}
	/* get raw param */
	public function getSegment($key=false, $end = false) {
		if(false === $key) {
			return $this->segment;
		}
		if(true === $end) {
			return implode('/',array_slice ($this->segment, $key));      
		}
		return true === isset($this->segment[$key]) ? $this->segment[$key] : null;
	}
	public function pathinfo() {
		return $this->pathinfo;
	}
	public function route(){
		// 파라메터가 없으면 default로 모듈,컨트롤러,엑션 순으로 매칭
		if(false === isset($this->route['null'])) {
			$this->route['null'] = array(
				':module/:controller/:action',
				$this->defaultModule."/".$this->defaultController."/".$this->defaultAction
			);
		}	
		if(false === isset($this->route['(.*)'])) {
			$this->route["(.*)"] = array(
				':module/:controller/:action/*', 
				$this->defaultModule."/".$this->defaultController."/".$this->defaultAction."/$1"
			);
			// 전체 매칭이 없으면 default로 모듈,컨트롤러,엑션 순으로 매칭
		}
		return $this->_parseUri($this->_getFixUri($this->route));	
	}
	private function _getPathinfo(){
		return (true === isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'],'/') : '');
	}
	private function _getAnchor(){
		return pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_BASENAME);
	}
	private function _getFixUri(array $array) {
		$strUri = '';
		$this->matchRoute = false;

		foreach($array as $key => $value) {		
			 if(isset($value[1]) && is_array($value[1])) {// (2011-02-23) 2번째 파라메터가 배열일경우 replacement는 null이고 기본변수에 배열 할당 
				$matching		= $value[0];
				$replacement	= null;
				$default		= @$value[1];
				$basedir		= @$value[2];
			} else {
				$matching		= $value[0];
				$replacement	= @$value[1];
				$default		= @$value[2];
				$basedir		= @$value[3];
			}

			if($key == '(.*)') {
				$replacement	= '$1'; // matching의 대상이 전체(.*) 일때는 replacement도 $1 한개
			} else if(false === isset($replacement) || true == is_null($replacement) ) {
				// replacement 갯수, 2개일경우 3개의 파라미터이며 추가파라미터 까지 4개의 치환자가 나와야 하므로 
				$_replacement_count = substr_count($matching, '/') + 2; 					
				$_replacement = array();
				for($i=1;$i<=$_replacement_count;$i++){
					$_replacement[] = '$'.$i; // replacement를 정규식의 치환자로 $1..$2...등으로 표시
				}
				$replacement= implode('/',$_replacement);
			}
			$target = "#^".$key."$#";
			if($key == 'forward') { // forward일때 검사조건을 쬐끔 줄여봄
				$this->matchRoute = array(
					$target,
					array(
						'matching'		=> $matching,
						'replacement'	=> $replacement,
						'default'		=> isset($default) ? $default : null,
						'basedir'		=> isset($bacedir) ? $bacedir : null,
					)
				);
				return $replacement;
			} else if(preg_match($target, $this->pathinfo)) {
				$this->matchRoute = array(
					$target,
					array(
						'matching'		=> $matching,
						'replacement'	=> $replacement,
						'default'		=> isset($default) ? $default : null,
						'basedir'		=> isset($bacedir) ? $bacedir : null,
					)
				);
				$strUri = preg_replace($target, $replacement, $this->pathinfo);//.'/'
				break;
			}
		}

		return (true === empty($strUri) ? $this->pathinfo : $strUri);
	}
	private function _split_raw($str) {
		if($str == '/') return array();
		return explode('/', $str);
	}
	private function _parseUri($uri='') { 

		$getseg		= true === isset($_GET) ? $_GET : array();
		$_vars		= array();
		$_seg		= rtrim($uri,'/');
		$_arrSeg	= $this->_split_raw($_seg.'/');

		$matching	= str_replace('*','',$this->matchRoute[1]['matching']);
		$arrMap		= trim($matching) != '' ? explode('/',rtrim($matching, '/')) : array();
		$slice		= (
						false === $this->matchRoute 
						|| (false === isset($this->matchRoute[1]['matching']) || true === empty($this->matchRoute[1]['matching']))
						? 3 : count($arrMap)
					);
		$_path		= array_splice($_arrSeg, $slice);
		if(true === isset($this->matchRoute[1]['basedir'])) {
			$_vars['basedir']		= $this->matchRoute[1]['basedir'];
		}
		if(false === isset($this->matchRoute[1]['matching']) || true === empty($this->matchRoute[1]['matching'])) { //매칭되는것이 없을때
			$_vars['module']	= $_arrSeg[0];
			$_vars['controller']= $_arrSeg[1];
			$_vars['action']	= $_arrSeg[2];
		} else {
			foreach($arrMap as $key => $value ) {
				if(true === isset($arrMap[$key]) && $arrMap[$key]) {
					$_vars[substr($arrMap[$key], 1)] = (true === isset($_arrSeg[$key]) ? $_arrSeg[$key] : '');
				}
			}
		}

		/*1. url 매칭*/
		for ($i=0, $max = count($_path) + 1; $i<=$max-2; $i+=2) { 
			if(true === isset($_path[$i]) && $_path[$i]) {
				if(true === isset($_path[$i+1])) {
					$_vars[$_path[$i]] = (true === isset($_path[$i+1]) ? $_path[$i+1] : '');
				}
			}
		} 

		/*2. 값이 없을 경우 default 파라메터 세팅*/
		if(true === isset($this->matchRoute[1]['default'])) {
			$tmp = $this->matchRoute[1]['default'];
			if(true === is_array($tmp)) {
				foreach($tmp as $key => $value) {
					$_k = ($key[0] == ':' ? substr($key, 1) : $key);
					if(isset($value[0]) && $value[0] == '!') { // important
						$_vars[$_k] = substr($value, 1);
					} else if(true === isset($_vars[$_k]) && $_vars[$_k]) {
					} else {
						$_vars[$_k] = $value;
					}
				}
			}
		}
		$__seg = $_vars + $getseg;	//앞에것 우선 == array_merge($getseg, $_vars) 뒤에것 우선
				
		$_sys_vars = array();		/* module/controller/action 없을경우 디폴트 셋팅 set이 되어있고 값이 없다면 빈값으로 인정 */
		$_sys_vars['module']		= false === isset($__seg['module'])		|| true === empty($__seg['module']) 
									? $this->defaultModule	: $__seg['module'];
		$_sys_vars['controller']	= false === isset($__seg['controller']) || true === empty($__seg['controller']) 
									? $this->defaultController	: $__seg['controller'];
		$_sys_vars['action']		= false === isset($__seg['action'])		|| true === empty($__seg['action']) 
									? $this->defaultAction	: $__seg['action'];
		$_sys_vars['basedir']		= false === isset($__seg['basedir'])	|| true === empty($__seg['basedir']) 
									? $this->defaultBasedir	: $__seg['basedir'];
		$_sys_vars['prefix']		= false === isset($__seg['prefix'])		|| true === empty($__seg['prefix']) 
									? $this->defaultPrefix	: $__seg['prefix'];	


		return $this->query = $__seg + $_sys_vars + (array)$this->matchRoute[1]['default'];
	} 
}