<?php

require_once("lime/bootstrap.php");

try {
	$router = new \lime\router(array(
		//'(?P<module>admin|order)(?:/(?P<parameter>.*))?' => array(), 
		'(?P<module>[^/]+)?(?:/(?P<year>[^/]+))?(?:/(?P<parameter>.*))?' => array(
			//'basedir' => 'test'
		)
	));
	$router->setErrorController('apps_error');

	$front = \lime\framework::getInstance();
	$front->setRouter($router);
	echo $front->dispatch();
} catch(\lime\SDOException $e) {
	pr($e);
} catch(\Exception $e) {
	pr($e);
}


/*
	$router = new \lime\router(array(
		'(.*)' => array(
			':module',
			array( // default
				'basedir'		=> 'apps'
				, 'module'		=> 'welcome'
				, 'controller'	=> 'run'
				, 'action'		=> 'index'
			)
		)
	));
	$router->setError('apps_error');

*/

/*
//exit();
pr(readable_size(memory_get_peak_usage()));
pr(readable_size(memory_get_usage()));
pr(get_included_files());
*/