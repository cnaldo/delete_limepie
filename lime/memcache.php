<?php

namespace lime;

class memcache
{
	// Some static properties.
	protected function __construct() { }
	protected static $_con = null;
	protected static $_driver = null;
	
	// Initialize the cache connection.
	public static function initialize($servers = array('127.0.0.1')) {
		if (class_exists('\Memcached')) {
			self::$_driver = 'Memcached';
			self::$_con = new \Memcached();
			foreach($servers as $server) {
				$server = explode(':', $server);
				$host = $server[0];
				$port = isset($server[1]) ? $server[1] : 11211;
				$weight = isset($server[2]) ? $server[2] : 100;
				self::$_con->addServer($host, $port, $weight);
			}
		} elseif (class_exists('\Memcache')) {
			self::$_driver = 'Memcache';
			self::$_con = new \Memcache();
			foreach($servers as $server) {
				$server = explode(':', $server);
				$host = $server[0];
				$port = isset($server[1]) ? $server[1] : 11211;
				$weight = isset($server[2]) ? $server[2] : 100;
				self::$_con->addServer($host, $port, true, $weight);
			}
		} else {
			throw new MemcacheException('Memcached extension is not available.');
		}
	}
	public static function get($key) {
		return self::$_con->get($key);		
	}
	public static function set($key, $value = null, $ttl = 3600) {
		if (self::$_driver === 'Memcached') {
			return self::$_con->set($key, $value, $ttl);
		} else {
			return self::$_con->set($key, $value, 0, $ttl);
		}
	}
	public static function delete($key) {
		return self::$_con->delete($key);
	}
	public static function callback($callback, $args = array(), $expires = 300) {
		// Create a unique ID for this callback item.
		$key = '@CallBack:' . md5(serialize($callback) . "\n" . serialize($args));
		
		// Look up the cached value.
		$value = self::get($key);
		
		// If not found, call the callback and cache the return value.
		if ($value === false) {
			$value = call_user_func_array($callback, $args);
			self::set($key, $value, $expires);
		}
		
		// Return the value.
		return $value;
	}
}

class MemcacheException extends Exception { }