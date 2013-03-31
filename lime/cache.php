<?php

namespace lime;

/**
 * 배열을 phpvar_export로 php파일 형태로 저장하는 캐시
 * 해당파일을 include하여 사용할수 있음
 *
 * $cache = new \lime\cache('serialize'); // serialize | array | json
 * $distinct_name = 'lala';
 * //$cache->clear($distinct_name); // 강제 삭제
 * $data = $cache->get($distinct_name); // 존재확인
 * if(!$data) {
 *	   $data = array('a','b','c','d','e','f','g');
 *     $cache->put($distinct_name, $data, 3600); // 생성
 * }
 * return $data;
 *
 * @package       system\cache
 * @category      system
 */

class CacheException extends \Exception 
{ 
}

class cache 
{
	private	$data	= array();
	private	$ext	= '.php';
	private	$type	= 'serialize'; // array/serialize
	public	$path	= '';

	public function __construct($type='') {
		$this->path = DATA_FOLDER.'cache'.DS;
		if($type) {
			$this->type = $type;
		}
	}
	private function _cache_name($name) {
		return $this->path.''.$this->type.'_cache_'.$name.$this->ext;
	}
	public function get($id) {
		if (isset($this->data[$id])) return $this->data[$id]; // Already set, return to sender
		echo $path = $this->_cache_name($id);

		if (\lime\file_exists($path) && \lime\is_readable($path)) { // Check if the cache file exists
			if($this->type == 'serialize') {
				$tmp		= unserialize(file_get_contents($path));
				$cache		= $tmp['cache'];
				$expires	= $tmp['expires'];
			} else if($this->type == 'json') {
				$tmp		= json_decode(file_get_contents($path), true);
				$cache		= $tmp['cache'];
				$expires	= $tmp['expires'];
			} else {
				require $path;
			}
			if (isset($expires) && $expires > 0 && $expires <= time()) {
				$this->clear($id);
				return false;
			} else {
				return (isset($cache)? $cache : false);
			}
		} else {
			return false;
		}
	} 
	public function put($id, $cache, $lifetime = 0) {
		$this->data[$id] = $cache;
		if (is_resource($cache)) {
			throw new CacheException("Can't cache resource.");
		}

		$path	= $this->_cache_name($id);
		make_dir(dirname($path));
		
		$fp		= @fopen($path, 'w');
		if (!$fp) {
			throw new CacheException('Unable to open file for writing.'.$path);
		}
		@flock($fp, LOCK_EX);

		$tmp = array(
			'cache' => $cache
		);
		if ($lifetime > 0) {
			$tmp['expires'] = (time()+$lifetime);
		} else {
			$tmp['expires'] = 0;
		}
		if($this->type == 'serialize') {
			$content = serialize($tmp);
		} else if($this->type == 'json') {
			$content = json_encode($tmp);
		} else {
			$data = phpvar_export($cache, true);			
			$content = '<?php $cache='.$data.';';
			if ($lifetime > 0) {
				$content .= '$expires='.(time()+$lifetime).';';
			}
			$content .= '?>';		
		}

		@fwrite($fp, $content);
		@flock($fp, LOCK_UN);
		@fclose($fp);
 
		if (\lime\file_exists($path)) chmod($path, 0777);
		else return false;
 
		return true;
	}
	public function clear($id) {
		if (isset($this->data[$id])) unset( $this->data[$id] ); // Already set, return to sender

		echo $path = $this->_cache_name($id);
		if (\lime\file_exists($path) && unlink($path)) {
			return true;
		} else {
			return false; //throw new CacheException('Cache could not be cleared.');
		}
	}
}
