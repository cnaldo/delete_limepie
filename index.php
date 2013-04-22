<?php 

require_once("lime/bootstrap.php");


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


/*
//exit();
pr(readable_size(memory_get_peak_usage()));
pr(readable_size(memory_get_usage()));
pr(get_included_files());
*/