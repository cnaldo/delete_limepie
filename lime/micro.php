<?php

namespace lime;

class micro {
	private $pathinfo; 
	private $segment;
	private $seg;
	private $route	= array();
	private $error;

	public function __construct($array = array()){
		$this->pathinfo	= $this->_getPathinfo();
		$this->segment	= explode('/',$this->pathinfo);
	}
	private function _getPathinfo(){
		return (true === isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'],'/') : '');
	}
	public function route($method, $pattern, $route) {
		$this->route[strtolower($method)][$pattern] = $route;
	}
	public function error($error_closure) {
		$this->error = $error_closure;
	}
	public function seg($key) {
		return isset($this->seg[$key]) ? $this->seg[$key] : '';
	}
	public function raw($i) {
		return isset($this->segment[$i]) ? $this->segment[$i] : '';
	}
	public function dispatch() {
		foreach($this->route[REQUEST_METHOD] as $key => $closure) {
			if(preg_match('#^'.trim($key,'/').'$#', $this->pathinfo, $match)) {
				array_shift($match);
				$pop	= array_pop($match);
				$_path	= trim($pop) != '' ? explode('/',rtrim($pop, '/')) : array();
		
				for($i=0,$max=count($_path)+1;$i<=$max-2;$i+=2) { 
					if(true === isset($_path[$i]) && $_path[$i]) {
						$this->seg[$_path[$i]] = (true === isset($_path[$i+1]) ? $_path[$i+1] : '');
					}
				} 

				$match[] = $pop;

				$reflection = new \ReflectionFunction($closure); 
				$tmp		= $reflection->getParameters(); 

				$param		= array();
				foreach($tmp as $k2 => $v2) {
					$this->seg[$v2->name] = isset($match[$k2]) ? $match[$k2] : '';
				}

				$closure = \Closure::bind($closure, $this);;
				return call_user_func_array($closure, $match);
			}
		}
		$closure = $this->error;
		$closure = \Closure::bind($closure, $this);;
		return call_user_func_array($closure, $match);
	}
}