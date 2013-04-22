<?php 

require_once("../lime/bootstrap.php");

class micro_apps extends \lime\micro
{
	public $ver = '100';
	public function __construct() {
		parent::__construct();
		\lime\Language::load('main');	
	}
	public function display($print = false) {
		$tpl = new \lime\tpl;
		$tpl->tpl_path		= '';
		$tpl->skin			= 'view/';
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

try {


	$micro = new micro_apps();
	$micro->bootstrap(function() {
		$this->layout = 'blue.tpl';
	});
	//phpinfo();

	$micro->route('GET', '/year2/([0-9]+)/([a-z0-9]+)/([a-z0-9]+)/?(.*)', function($year2 = '2013', $a, $b) {
		//pr($this);
		//secho $param;

		pr( $this->getQuery('c') );
		pr ( $this->getSegment(0) );
		return $year2 + 4;
	});

	$micro->error(function() {
		echo 'not found';
	});
	echo $micro->dispatch();

} catch(\lime\TplException $e) {
	echo 'test';
	pr($e);
} catch(\Exception $e) {
	pr($e);
}
