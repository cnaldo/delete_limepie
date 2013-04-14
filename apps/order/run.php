<?php

set_time_limit(0);

class order_run extends apps_globals {
	public $layout = 'blue.tpl';
	public function __construct() {
		parent::__construct();
		\lime\lang('order');
	}
	protected function test() {
		echo 'a';
	}
	public function xlayout(){}
	public function info() {
		phpinfo();
	}
	private function create_rules() {
		$rules = new \lime\validate\rules;

		$rules->name('firstname[]')
			 // ->required(true, '이름은 필수입니다.')
			  ->mincount(2, '이름을 2개 입력하세요.')
			  ->match('([a-z]+)', '이름은 a-z만 허용됩니다.');
/*
		$rules->name('lastname')
			  ->required(true, "입력하세요 lastname");

		$rules->name('username')
			  ->required(true, "입력하세요 username")
			  ->minlength(2, "입력하세요 2 characters");

		$rules->name('password')
			  ->required(true, "입력하세요 password")
			  ->minlength(5, "Your password must be at least 5 characters long");

		$rules->name('confirm_password')
			  ->required(true, "Please provide a password")
			  ->minlength(5, "Your password must be at least 5 characters long")
			  ->equalTo("#password", "Please enter the same password as above");

		$rules->name('email')
			  ->required(true, "Please enter a valid email address")
			  ->email(true);
*/
		$rules->name('topic[]')
			  ->required(true)
			  ->mincount(2, '토픽을 2개 이상 선택하세요.');

		$rules->name('agree')
			  ->required(true, "Please accept our policy");

		return $rules->save();
	}
	public function get_index() {
	
		$s = array(
			'a'=>'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'
		);
		try {
			$key = 'a';
			$e = \lime\crypt::pack($s, $key);
			$d = \lime\crypt::unpack($e, $key);
		} catch(\lime\cryptException $e) {
			echo 'crypt err';
		}
		$this->set(array(
			'obj'		=> $this
			, 'rules'	=> json_encode($this->create_rules())
		));

		$this->content('read.tpl');
		return $this->display(true);
	}

	public function post_index() {
		$valid	= \lime\validate::run($this->create_rules());
		if($valid) {
			pr($valid);
		}	
	}	
}

