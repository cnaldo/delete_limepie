<?php

namespace lime;

class SDOException extends \PDOException {

    public function __construct(\PDOException $e) {
//pr($e->getTrace());
		foreach($e->getTrace() as $key => $value) {
			if((isset($value['class']) && $value['class'] == 'lime\\database')
			 || (isset($value['class']) && $value['class'] == 'PDOStatement')
			 || (isset($value['function']) && $value['function'] == 'call_user_func_array')
			 || (isset($value['function']) && $value['function'] == '__callStatic')) {
				continue;
			}

			$last = $value;
			break;
		}
		pr($last);
		pr($e->getMessage());
		exit();
    }
} 

class db 
{
	static public $driver = null;
	static public $instance = array();

	static public function conn($conn = 'master') {
		return db::getInstance($conn);
	}

	static public function getInstance($conn = 'master') {
		$conn = ENVIRONMENT.'\\'.$conn;

		if(self::$driver == null) {
			/*file load*/
			if ((self::$driver = @parse_ini_file(CONFIG_FOLDER.'ini.db.php', true)) == false) {
				throw new SDOException('Missing INI file or Parse Error : ' . CONFIG_FOLDER.'ini.db.php');
			}
		}
		if(false == isset(self::$driver[$conn])) {
			throw new SDOException($conn.' db driver not found');
		}
		$config		= self::$driver[$conn];
		
		$tmp		= parse_url($config['dsn']);
		$config_md5	= md5(serialize($config));

		try {
			if (false === isset(self::$instance[$config_md5])) {			
				if(file_exists(SYSTEM_FOLDER.'db/driver/'.$tmp['scheme'].'.php')) {
					require_once(SYSTEM_FOLDER.'db/driver/'.$tmp['scheme'].'.php');

					$tmp = parse_url($config['dsn']);
					$driver = '\lime\db\driver\\'.$tmp['scheme'];

					self::$instance[$config_md5] = new $driver($config['dsn'], $config['username'], $config['password'], $config);;
					self::$instance[$config_md5]->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
					self::$instance[$config_md5]->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC); 
					self::$instance[$config_md5]->scheme = $tmp['scheme'];;
				} else {
					throw new SDOException($tmp['scheme'].' driver not found');
				}
			}
			return true === isset(self::$instance[$config_md5]) ? self::$instance[$config_md5] : false;
		} catch (\PDOException $e) {
			throw new SDOException($e);
		}
	}	
}


/**
 * pdo를 확장하여 get,gets,set,sequence 등의 메소드 제공
 * driver별 sets, limit, sequence 등의 메소드 제공 
 *
 * @package       system\dao
 * @category      system
 */

interface iDatabase
{
	public function get($statement, $bind_parameters, $mode);
	public function gets($statement, $bind_parameters, $mode);
	public function get1($statement, $bind_parameters, $mode);
	public function limit();
	public function set($statement, $bind_parameters);
	public function sets($statement, $bind_parameters);
	public function setid($statement, $bind_parameters);
	public function sequence();
	public function begin();
	public function rollback();
	public function commit();
}

abstract class database extends \pdo
{	
	public static $is_transaction = false;
	public static $debug = true;
	public static $commit_count = 0; // 모델단에서 트랜젝션이 일어났을때 카운트로 commit시점을 조절한다.
	public $scheme = null;

	public function __construct($dsn, $username = '', $password = '', $driver_options = array()) {
		parent::__construct($dsn, $username, $password, $driver_options);
	}
	public function getlog($statement, $bind_parameters, $start) {

		list($qstr, $explain_data )= $this->get_explain($statement, $bind_parameters);
		$r = array(
			'__start_time'	=> $start,
			'execute_time'	=> php_timer(),
			'query'			=> $qstr,
			'explain'		=> $explain_data,
		);
		return $r;
	}
	public function setlog($statement, $bind_parameters, $start) {
		return true;
		putdblog(self::getlog($statement, $bind_parameters, $start));	
	}
	public function get_explain($query, $bind_parameters) {
		if(false == preg_match('/^([\s]+)?select/i',$query)) {
			return array('', '');
		}

		$sql = 'explain '.$query;

		$statement = $query;
		if( 0 < count($bind_parameters)) {
			foreach($bind_parameters as $key => $value) {
				$statement	= preg_replace('/'.$key.'(,|;| |\r|\n|\t|$|\))/', (true === is_int($value) && true === is_numeric($value) ? $value : "'".$value."'") ."\\1", $statement);
			}
		}		
		$tmp = '';
		$tmp .= '<style>.explain th {text-align:center;} .explain th, .explain td {padding:5px;FONT-SIZE:9PT;} .explain th {background:#efefef}</style><table cellpadding=5 cellspacing=1 border=1 bordercolor=black class="explain" style="table-layout:fixed;word-break:break-all;width:100%"><tr><th width=30>id</th><th width=70>select_type</th><th>table</th><th width=50>type</th><th>possible_keys</th><th>key</th><th WIDTH=50>key_len</th><th>ref</th><th width=50>rows</th><th width=80>Extra</th></tr>';


		$stmt = self::execute($sql, $bind_parameters);
		$q = $stmt->fetchAll();

		foreach ($q as $row) {

			$tmp .= "<tr><td >".$row['id']."</td><td>".str_replace(' ','<br>',$row['select_type'])."</td><td>".$row['table']."</td><td>".$row['type']."</td><td>".str_replace(",","<br />",$row['possible_keys'])."</td><td>".$row['key']."</td><td>".$row['key_len']."</td><td>".str_replace('.','<br>',$row['ref'])."</td><td align=right>".$row['rows']."</td><td>".str_replace('; ',';<br>',$row['Extra'])."</td></tr>";
		}
		return array($statement, $tmp.'</table>');			
	}
	private function execute($statement, $bind_parameters = array(), $ret = false) {
		$stmt				= parent::prepare($statement);
		$_bind_parameters	= array();
		foreach($bind_parameters as $key => $value) {
			if(is_array($value)) {
				$_bind_parameters[$key] = $value[0];
			} else {
				$_bind_parameters[$key] = $value;
			}
		}

		$result = $stmt->execute($_bind_parameters);
		if(true === $ret) {
			$stmt->closeCursor();
			return $result;
		} else {
			return $stmt;
		}
	}
	public function gets($statement, $bind_parameters = array(), $mode = null) {
		try {
			$start	= php_timer();
			$stmt	= self::execute($statement, $bind_parameters);
			$mode	= self::_getMode($mode);
			$result	= $stmt->fetchAll($mode);
			$stmt->closeCursor();
			if(self::$debug == true) {
				self::setlog($statement, $bind_parameters, $start);
			}
			return $result;
		} catch (\PDOException $e) {
			self::rollback();
			if( self::$debug == true) {
				throw new SDOException($e);
			}
		}
	}
	public function get($statement, $bind_parameters = array(), $mode = null) {
		try {
			$start	= php_timer();
			$stmt	= self::execute($statement, $bind_parameters);
			$mode	= self::_getMode($mode);
			$result	= $stmt->fetch($mode);
			$stmt->closeCursor();
			if(self::$debug == true) {
				self::setlog($statement, $bind_parameters, $start);
			}
			return $result;
		} catch (\PDOException $e) {
			self::rollback();
			if( self::$debug == true) {
				throw new SDOException($e);
			}
		}
	}
	/* count(*)과 같이 하나의 값만 리턴할경우 $tmp[0]과 같이 사용하지 않고 바로 $tmp에 select한 값을 셋팅함*/
	public function get1($statement, $bind_parameters = array(), $mode = null) {
		$start	= php_timer();
		$mode	= self::_getMode($mode);
		$result	= self::get($statement, $bind_parameters, $mode);
		if(self::$debug == true) {
			self::setlog($statement, $bind_parameters, $start);
		}
		if(is_array($result)) {
			foreach($result as $key => $value) {
				return $value;
			}
		}
		return false;
	}
	private function _getMode($mode = null) {
		if($mode == null) {
			$mode = self::getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE);
		}
		return $mode;
	}
	public function set($statement, $bind_parameters = array()) {
		try {
			return self::execute($statement, $bind_parameters, true);
		} catch (\PDOException $e) {
			self::rollback();
			if( self::$debug == true) {
				throw new SDOException($e);
			}
		}
	}
	public function close() { 
		return self::commit(); 
	}
	public function start() {
		return self::begin();
	}
	public function begin() { 
		self::$commit_count++; 
		if(true === self::$is_transaction) {
			return; 
		}
		if($return = parent::beginTransaction()) {
			self::$is_transaction = true; 
			return $return;
		}
	} 
	public function rollback() { 
		if(false === self::$is_transaction) return; 

		if($return = parent::rollBack()) {
			self::$is_transaction = false; 
			return $return;
		}
	} 
	public function commit() { 
		self::$commit_count--;
		if(false === self::$is_transaction) return; 

		if(self::$commit_count == 0 && $return = parent::commit()) {
			self::$is_transaction = false; 
			return $return;
		}
	} 
}
/*
function setdblog() {
	dao()->set_debug(true);
}
function putdblog($arg = array(), $val = null) {
	$a = bank()->dblogs;
	bank()->dblogs = array_merge($a, array($arg));
}
function getdblog() {
	foreach( bank()->dblogs as $key => $value) {
		printdblog($value);
	}
}
function printdblog($value) {
	echo '<center><table border=1 cellpadding=5 cellspacing=1 border=1 bordercolor=black class="explain" style="width:1200px;table-layout:fixed;word-break:break-all;white-space: pre-wrap;white-space: -moz-pre-wrap;white-space: -pre-wrap;white-space: -o-pre-wrap;">
		<tr><th width="100">start time</th><td>'.$value['__start_time'].'</td></tr>
		<tr><th>excute time</th><td>'.$value['execute_time'].'</td></tr>
		<tr><th>query</th><td><pre>'.$value['query'].'</pre></td></tr>
		<tr><th>explain</th><td>'.$value['explain'].'</td></tr>
	</table></center><br /><br />';
}

*/