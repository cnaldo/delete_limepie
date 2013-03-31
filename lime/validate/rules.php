<?php

namespace lime\validate;

class rules 
{
	public $rules		= array();
	public $messages	= array();
	public $name		='';
	function name($name) {
		$this->name = $name;
		return $this;
	}
	function email($email, $message='') {
		$this->rules[$this->name]['email']				= $email;
		if($message) {
			$this->messages[$this->name]['email']		= $message;
		}
		return $this;
	}
	function url($url, $message='') {
		$this->rules[$this->name]['url']				= $url;
		if($message) {
			$this->messages[$this->name]['url']			= $message;
		}
		return $this;
	}
	function match($match, $message='') {
		$this->rules[$this->name]['match']				= $match;
		if($message) {
			$this->messages[$this->name]['match']		= $message;
		}
		return $this;
	}
	function maxlength($maxlength, $message='') {
		$this->rules[$this->name]['maxlength']			= $maxlength;
		if($message) {
			$this->messages[$this->name]['maxlength']	= $message;
		}
		return $this;
	}	
	function minlength($minlength, $message='') {
		$this->rules[$this->name]['minlength']			= $minlength;
		if($message) {
			$this->messages[$this->name]['minlength']	= $message;
		}
		return $this;
	}
	function rangelength($rangelength, $message='') {
		$this->rules[$this->name]['rangelength']		= $rangelength;
		if($message) {
			$this->messages[$this->name]['rangelength']	= $message;
		}
		return $this;
	}
	function max($max, $message='') {
		$this->rules[$this->name]['max']				= $max;
		if($message) {
			$this->messages[$this->name]['max']			= $message;
		}
		return $this;
	}	
	function min($max, $message='') {
		$this->rules[$this->name]['min']				= $min;
		if($message) {
			$this->messages[$this->name]['min']			= $message;
		}
		return $this;
	}
	function range($max, $message='') {
		$this->rules[$this->name]['range']				= $range;
		if($message) {
			$this->messages[$this->name]['range']		= $message;
		}
		return $this;
	}	
	function equalTo($name, $message='') {
		$this->rules[$this->name]['equalTo']			= $name;
		if($message) {
			$this->messages[$this->name]['equalTo']		= $message;
		}
		return $this;
	}
	function remote($remote, $message='') {
		$this->rules[$this->name]['remote']				= $remote;
		if($message) {
			$this->messages[$this->name]['remote']		= $message;
		}
		return $this;
	}				
	function required($bool, $message='') {
		$this->rules[$this->name]['required']			= $bool;
		if($message) {
			$this->messages[$this->name]['required']	= $message;
		}
		return $this;
	}
	function save() {
		return array(
			'rules'		=> $this->rules,
			'messages'	=> $this->messages
		);
	}
	function json() {
		return json_encode($this->save());		
	}
}