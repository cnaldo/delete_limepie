<?php

namespace lime\db\driver;

class mysql extends \lime\database implements \lime\iDatabase   
{
	public function __construct($dsn, $username = '', $password = '', $driver_options = array()) {
		$driver_options[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
		if(isset($driver_options['charset']) && $driver_options['charset']) {
			$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES ".$driver_options['charset'];
		}
		
		if(isset($driver_options['profiling']) && $driver_options['profiling']) {
			if(!$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND]) {
				$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET ';
			} else {
				$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = ', ';			
			}
			$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] .= " PROFILING = 1";
		}
		//	$driver_options[\PDO::ATTR_PERSISTENT] = true;
		parent::__construct($dsn, $username, $password, $driver_options);
	}
	public function sequence($name=null) {
		$this->set("UPDATE sequence_table SET seq = LAST_INSERT_ID( seq + 1 )");
		return $this->get1("SELECT LAST_INSERT_ID( )");
	}
	public function insertId($name=null) {
		return $this->get1('SELECT LAST_INSERT_ID()');
	}
	public function limit() {
		$args_list	= func_get_args();
		$args_count	= count($args_list);
		$sql = $args_list[0];
		if($args_count == 3) {
			$sql .=' limit '.$args_list[1].', '.$args_list[2];
		} else {
			$sql .=' limit '.$args_list[1];
		}
		return $sql;
	}
	public function __sets($statement, $arr_bind_parameters = array()) {	/*배열을 잘라 여러번 시도*/
		if(count($arr_bind_parameters) < 1) {
			return -1;
		}
		preg_match('/(.*)values([^\(]+)?\((.*)\)/is',$statement,$m);
		
		$s = explode(',',preg_replace('/\s/','',$m[3]));

		$array = array_chunk($arr_bind_parameters, 50, true);
		$i = 0;
		$this->begin();
		foreach($array as $rkey => $bind_parameters) {
			$new_statmement = array();
			$new_bind_parameters = array();
			foreach($bind_parameters as $key => $value) {
				$b = array();
				foreach($s as $k2 => $v2) {
					$b[] = $v2.$key;
					$new_bind_parameters[$v2.$key] = $value[$v2];
				}
				$new_statmement[] = "(".implode(', ',$b).")";
				$i++;
			}
			$new_statmement = $m[1].' values '.implode(', ',$new_statmement);

			if( $this->set($new_statmement, $new_bind_parameters) ) {

			} else {
				$this->rollback();
				return false;
			}
		}
		$this->commit();
		return $i;
	}
	public function sets($statement, $bind_parameters = array()) {
		if(count($bind_parameters) < 1) {
			return -1;
		}
		preg_match('/(.*)values([^\(]+)?\((.*)\)/is',$statement,$m);
		
		$s = explode(',',preg_replace('/\s/','',$m[3]));

		$new_statmement = array();
		$new_bind_parameters = array();
		$i = 0;
		foreach($bind_parameters as $key => $value) {
			$b = array();
			foreach($s as $k2 => $v2) {
				$b[] = $v2.$key;
				$new_bind_parameters[$v2.$key] = $value[$v2];
			}
			$new_statmement[] = "(".implode(', ',$b).")";
			$i++;
		}
		$new_statmement = $m[1].' values '.implode(', ',$new_statmement);

		if( $this->set($new_statmement, $new_bind_parameters) ) {
			return $i;
		} else {
			return false;
		}
	}
	public function setid($statement, $bind_parameters = array()) {	/* return int or false */
		if($this->set($statement, $bind_parameters)) {
			return $this->insertid();
		}
		return false;
	}
	public function set_fkchk($chk = 0) {
		return self::set("SET FOREIGN_KEY_CHECKS=:chk", array(':chk'=>$chk));
	}
}
