<?php 

require_once("lime/bootstrap.php");



$micro = new apps_micro();

$micro->route('GET', '', function() {
	$this->content('read.tpl');
	return $this->display();
});

$micro->route('GET', '/year/([0-9]+)', function($year = '2013', $a=0, $b='') {
	$micro->seg('');
	return $year + 2;
});

$micro->route('GET', '/year2/([0-9]+)/([a-z0-9]+)/([a-z0-9]+)', function($year2 = '2013', $a=0, $b='') {
	$micro->seg('');
	return $year2 + 4;
});

$micro->route('GET', '/year2/([0-9]+)/([a-z0-9]+)/([a-z0-9]+)/?(.*)', function($year2 = '2013', $a, $b) {
	pr($this);
	//secho $param;
	echo $this->seg('c');
	echo $this->raw(0);
	return $year2 + 4;
});

$micro->error(function() {
	echo 'not found';
});

echo $micro->dispatch();

/*
exit();
try {
	$router = new \lime\router(array(
		'(.*)' => array(
			':module/:action',
			array( // default
				'basedir'		=> 'apps'
				, 'module'		=> 'welcome'
				, 'controller'	=> 'run'
				, 'action'		=> 'index'
			)
		)
	));
	$router->setError('apps_error');
	$front = \lime\framework::getInstance();
	$front->setRouter($router);
	echo $front->dispatch();
} catch(\lime\SDOException $e) {
	pr($e);
} catch(\Exception $e) {
	pr($e);
}

*/

/*
//exit();
pr(readable_size(memory_get_peak_usage()));
pr(readable_size(memory_get_usage()));
pr(get_included_files());
*/