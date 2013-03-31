<?php

namespace lime;

class TplException extends \Exception 
{ 
}

class tpl 
{
	public $tpl_ = array();
	public $var_ = array();
	public function __construct() {
		$this->notice			=true;
		$this->tpl_path			='';
		$this->skin				='';
		$this->environment		='';
		$this->compile_path		=rtrim(DATA_FOLDER,"/")."/tpl";
	}	
	function assign($arg) {
		if (is_array($arg)) {
			$this->var_ = array_mix( $arg , $this->var_);
		} else {
			if(count(func_get_args())>1) {
				if(false == isset($this->var_[$arg])) {
					$this->var_[$arg] = array();
				}
				$this->var_[$arg] = func_get_arg(1);//array_mix (func_get_arg(1), $this->var_[$arg]);
			}
		}
	}
	public function define($arg, $path='') {
		if ($path) $this->_define($arg, $path);
		else foreach ($arg as $fid => $path) $this->_define($fid, $path);
	}
	public function _define($fid, $path) {
		if(is_array($path)) {
			$this->tpl_[$fid] = array('string', $path);// string array로 직접 넘김
		} else if ($fid[0] == '>') {
			$this->tpl_[substr($fid,1)] = array('php', array($path,'',''));//
		} else {
			$this->tpl_[$fid] = array('file', array($path,'',''));//
		}
	}
	public function _template_notice_handler($type, $msg, $file, $line)
	{
		switch ($type) {
		case E_NOTICE      :$msg='<b>Template_ Notice #1</b>: '.$msg.'';break;
		case E_WARNING     :
		case E_USER_WARNING:$msg='<b>Warning</b>: '.$msg." in <b>$file</b> on line <b>$line</b>"; break;
		case E_USER_NOTICE :$msg='<b>Notice</b>: ' .$msg." in <b>$file</b> on line <b>$line</b>"; break;
		case E_USER_ERROR  :$msg='<b>Fatal</b>: '  .$msg." in <b>$file</b> on line <b>$line</b>"; break;
		default            :$msg='<b>Unknown</b>: '.$msg." in <b>$file</b> on line <b>$line</b>"; break;
		}
		echo "<br />".$msg."<br />";
	}
	public function display($fid, $ret = false) {
		if($ret) {
			return $this->fetch($fid);
		} else {
			$this->render($fid);
		}
	}
	public function fetch($fid) {
		ob_start();
		$this->render($fid);
		$fetch = ob_get_contents();
		ob_end_clean();
		return $fetch;
	}
	public function render($fid) {
		$tpl_path		= $this->tpl_path($fid);
		$compile_path	= $this->compile_path.str_replace(HTDOCS_FOLDER, DS, $tpl_path).".php";

		if(ENVIRONMENT == 'production') {
			$this->_include_tpl($compile_path, $fid);//, $scope);
		} else {
			if(false == \lime\file_exists($tpl_path)) {
				throw new TplException('템플릿 파일이 없음 : '.$tpl_path);
			}

			$time			= \lime\filemtime($tpl_path);
			$compile_time	= \lime\file_exists($compile_path) ? \lime\filemtime($compile_path) : 0;

			if($this->environment == 'development' || $time != $compile_time) {
				$compiler = new \lime\tpl\compiler;
				$compiler->compile($fid, $this);
				//$this->compile($fid);
				touch($compile_path, $time);		
			}
			$this->ebase=error_reporting();
			if ($this->notice) {
				error_reporting($this->ebase|E_NOTICE);
				set_error_handler(array($this, '_template_notice_handler'));
				$this->_include_tpl($compile_path, $fid);//, $scope);
				restore_error_handler();
			} else {
				error_reporting($this->ebase&~E_NOTICE);
				$this->_include_tpl($compile_path, $fid);//, $scope);
			}
			error_reporting($this->ebase);
		}

	}
	public function _include_tpl($TPL_CPL, $TPL_TPL) {//, $TPL_SCP)
		extract($this->var_);
		if (false===include $TPL_CPL) {
			throw new TplException('#'.$TPL_TPL.' include error '.$TPL_CPL);
		}
	}
	public function tpl_path($fid) {
		$path = $this->tpl_[$fid][1][0];
		if($path[0] == '/') {
			return $path;
		} else {
			$a = trim($this->skin,'/');
			return rtrim($this->tpl_path,'/')."/".($a ? $a.'/' : '').$path;
		}
	}
}
