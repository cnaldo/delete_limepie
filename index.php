<?php


$string = '김이박/감사(오늘도/제대로걸림)/조치원/원주/상파울로(브라질/수도)'; 
$data = preg_split('#(?=(?![^(]*\)))/#',$string); 
echo '<pre>';
print_r($data); 
echo '</pre>';

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
//exit();
pr(readable_size(memory_get_peak_usage()));
pr(readable_size(memory_get_usage()));
pr(get_included_files());
*/