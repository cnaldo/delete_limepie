<?php

namespace lime\tpl;

class CompileException extends \Exception 
{ 
}

class compiler {
	public $otpl;
	public function __construct() {
		$this->plugin_dir		=SYSTEM_FOLDER.'tpl/plugin';
		$this->postfilters		=array();
		$this->postfilter		='';
		$this->prefilter		='';
		$this->obj_plugins		=array();
		$this->quoted_str		='(?:"(?:\\\\.|[^"])*")|(?:\'(?:\\\\.|[^\'])*\')';
		$functions				=get_defined_functions();
		$this->all_functions	=array_merge(
									$functions['internal'],
									$functions['user'],
									array('isset','empty','eval','list','array','include','require','include_once','require_once')
								);
		$this->on_ms			=substr(__FILE__,0,1)!=='/';
		$this->func_plugins		=array();
		$this->func_list		=array(''=>array());
		$this->obj_list			=array(''=>array());
		$this->rsv_words		=array('index_', 'size_', 'key_', 'value_', 'last_');
		$this->key_words		=array('true','false','null');
		$this->auto_globals		=array('_SERVER','_ENV','_COOKIE','_GET','_POST','_FILES','_REQUEST','_SESSION');
		$this->constants		=array_keys(get_defined_constants());
		$this->auto_constant	=false;
		$this->safe_mode		=false;
		$this->method_list		=array();
		$this->_loop_stack		=array();
	}
	public function compile($fid, $otpl) {
		$this->otpl							= $otpl;
		$tpl_path							= $this->otpl->tpl_path($fid);
		if(\lime\file_exists($tpl_path)		=== false) {
			throw new CompileException('define	: '.$fid.' template path : '.$tpl_path);
		}
		$source								= file_get_contents($tpl_path);
		$source								= $this->_execute($source, $fid);
		$compile_path						= $this->otpl->compile_path.str_replace(HTDOCS_FOLDER, DS,$tpl_path).".php";
		$folder								= dirname($compile_path);
		if(false == \lime\file_exists($folder)) {
			mkdir($folder, 0777, true);
		}
		file_put_contents($compile_path, $source,  LOCK_EX);
	}
	public function _filter($source, $type) {
		$this->ebase	=error_reporting();
		error_reporting(E_ALL);
		$func_split		=preg_split('/\s*(?<!\\\\)\|\s*/', trim($this->otpl->{$type.'filter'}));
		$func_sequence	=array();
		for ($i=0,$s=count($func_split); $i<$s; $i++) if ($func_split[$i]) $func_sequence[]=str_replace('\\|', '|', $func_split[$i]);
		if (!empty($func_sequence)) {
			for ($i=0,$s=count($func_sequence); $i<$s; $i++) {
				$func_args=preg_split('/\s*(?<!\\\\)\&\s*/', $func_sequence[$i]);
				for ($j=1,$k=count($func_args); $j<$k; $j++) {
					$func_args[$j]=str_replace('\\&', '&', trim($func_args[$j]));
				}
				$func = strtolower(array_shift($func_args));
				$func_name   = $this->{$type.'filters_flip'}[$func];
				array_unshift($func_args, $source, $this);
				$func_file = $this->plugin_dir.'/'.$type.'filter.'.$func_name.'.php';
				if (!in_array($func, $this->{$type.'filters'})) {
					throw new CompileException('cannot find '.$type.'filter file <b>'.$func_file.'</b>');
				}
				if (!function_exists($func_name)) {
					if (false===include_once $func_file) {
						throw new CompileException('error in '.$type.'filter <b>'.$func_file.'</b>');
					} elseif (!function_exists($func_name)) {
						throw new CompileException('filter function <b>'.$func_name.'()</b> is not found in <b>'.$func_file.'</b>');
					}
				}
				$source=call_user_func_array($func_name, $func_args);
			}
		}
		error_reporting($this->ebase);
		return $source;
	}
	private function _execute($source, $fid) {
		$this->tpl_dir	= $this->otpl->tpl_path($fid);
		$plugins		= array();
		$match			= array();
		$d				= dir($this->plugin_dir);
		if (false === $d) {
			throw new CompileException('cannot access plugin directory <b>'.$this->plugin_dir.'</b>');
		}

		while ($plugin_file = $d->read()) {
			$plugin_path = $this->plugin_dir .'/'. $plugin_file;
			if (!is_file($plugin_path) || !preg_match('/^(object|function|prefilter|postfilter)\.([^.]+)\.php$/i', $plugin_file, $match)) continue;
			$plugin =strtolower($match[2]);
			if ($match[1] === 'object') {
				if (in_array($plugin, $this->obj_plugins)) {
					throw new CompileException('plugin file <b>object.'.$match[2].'.php</b> is overlapped');
				}
				$this->obj_plugins[$match[2]] = $plugin;
			} else {
				switch ($match[1]) {
				case 'function'  : $this->func_plugins[$match[2]]=$plugin; break;
				case 'prefilter' : $this->prefilters[$match[2]]  =$plugin; break;
				case 'postfilter': $this->postfilters[$match[2]] =$plugin; break;
				}
				if (in_array($plugin, $plugins)) {
					throw new CompileException('plugin function <b>'.$plugin.'</b> is overlapped');
				}
				$plugins[]=$plugin;
			}
		}
		$this->obj_plugins_flip = array_flip($this->obj_plugins);
		$this->func_plugins_flip= array_flip($this->func_plugins);
		$this->prefilters_flip  = array_flip($this->prefilters);
		$this->postfilters_flip = array_flip($this->postfilters);
		if (trim($this->otpl->prefilter)) $source=$this->_filter($source, 'pre');
		$source = str_replace(chr(13).chr(10), chr(10),$source);// convert new line 
		$source = preg_replace('/^\xEF\xBB\xBF/', '', $source);// remove UTF-8 BOM
		$source = preg_replace('#{\*(.*)\*}#Use','str_repeat(chr(10),substr_count("$1",chr(10)))',$source); // 일반적인 주석을 삭제, 줄맞춤

		preg_match_all('#(.*)?({)([^{]*)(})#Us',$source.'{__hidden__}',$var_token);
		$ifchk	= $elseifchk = 0;
		$string	= '';
		$branch	= array();
		if(isset($var_token[3]) == true && count($var_token[3]) > -1) {
			foreach($var_token[3] as $key => $value) {
				$v = trim($value);
				$pre_append = $var_token[1][$key];
				$line = substr_count($pre_append.$string, chr(10))+1;
				if(in_array(substr($v,0, 1), array('\\'))) { // escape
					$string .= $pre_append.'{'.substr($value,1).'}';
				} else if(in_array(substr($v,-1), array(';'))) { // javascript, pass
					$string .= $var_token[0][$key];
				} else if($v == '__hidden__') { // system, pass
					$string .= $pre_append;
				} else if(substr($v,0,1) == '*' && substr($v,-1) == '*') { // comment, pass
					$string .= $pre_append.str_repeat(chr(10),substr_count($value,chr(10))); // line matching
				} else {
					// 첫자의 기호를 분석
					$fixed_value	= ($value);
					$type			= substr(trim($fixed_value), 0, 1);
					$elsetype		= substr(trim($fixed_value), 0, 2);
					if($elsetype == '//') {
						$string .= $pre_append.'{'.$value.'}';
					} else if($type == '#') {
						$_fid = substr($fixed_value, 1);
						$string .= $pre_append.'<?php $this->render("'.$_fid.'");?>';
						// $string .= $pre_append.$this->compile($_fid);
					} else if($type == '/') {
						array_pop($branch);
						$c = end($branch);
						if(false == isset($c[0])) {
							$this->report('Error #9', 'line : '.$line.' template loop or branch is not properly closed by <b>{/}</b>', true);
							$this->exit_();
						} else {
							array_pop($branch);
							$string .= $pre_append.'<?php '.'}}?>';
						}
					} else if($type == ':' && $elsetype != ':?') {
						$c = end($branch);
						if($c[0] != 'if') {
							$this->report('Error #20 108', 'not if error : line : '.$line.' '.$fixed_value,true,true);
							$this->exit_();
						}
						$string .= $pre_append.'<?php '.'}} else {{ ?>';
					} else if($type == '?' || $elsetype == ':?') {
						if($elsetype == ':?') {
							$c = end($branch);
							if($c[0] != 'if' && $c[0] != 'elseif') {
								$this->report('Error #20 116', 'not if error : line : '.$line.' '.$fixed_value,true,true);
								$this->exit_();
							}
							$branch[] = array('elseif',$line);
							$branch[] = array('if', $line);
							$fixed_value = substr($fixed_value, 2);
							if($str = ($this->build_var($fixed_value))) {
								$string .= $pre_append.'<?php '.'}} else if('.$str.') {{ ?>';
							} else {
								$this->report('Error #20', 'else if error : line : '.$line.' '.$fixed_value,true,true);
								$this->exit_();
								$string .= $pre_append.'{:?'.$fixed_value.'}';
							}
						} else {
							$branch[] = array('if', $line);
							$branch[] = array('if', $line);
							$fixed_value = substr($fixed_value, 1);
							if($str = ($this->build_var($fixed_value))) {
								$string .= $pre_append.'<?php '.'if('.$str.') {{ ?>';
							} else {
								$this->report('Error #20', 'if error : line : '.$line.' '.$fixed_value,true,true);
								$this->exit_();
								$string .= $pre_append.'{?'.$fixed_value.'}';
							}
						}
					} else if($type == '@') {
						// 키값은 단순 스트링이다 
						$fixed_value = substr($fixed_value, 1);
						$tmp = explode("=", $fixed_value, 2);
						$loop_key = trim($tmp[0]);
						$loop_value = trim($tmp[1]);
						if(preg_match('#[[a-zA-Z][a-zA-Z0-9\._]{0,}#', $loop_value)) {
							$loop_key2   = $this->build_var($loop_key);
							if(!preg_match('#^[[a-zA-Z][a-zA-Z0-9_]{0,}$#', $loop_key)) {
								$this->report('Error #20', '키할당 변수는 배열일수 없음 error : line : '.$line.' '.$loop_key,true,true);
								$this->exit_();
							} else if(!$loop_key2) {
								$this->report('Error #20', '키할당 error : line : '.$line.' '.$loop_key,true,true);
								$this->exit_();
							}
							$loop_value2 = $this->build_var($loop_value);
							if(!$loop_value2) {
								$this->report('Error #20', '키내용 error : line : '.$line.' '.$loop_value,true,true);
								$this->exit_();
							}

							$string .= $pre_append.$this->loop($loop_key2, $loop_value2);//'<?php '.'if(true == is_array('.$loop_value.') {foreach('.$loop_value.' AS '.$this->get_word($loop_key).') {{? >';
							$branch[] = array('for', $line);
							$branch[] = array('if', $line);
						} else {
							$string .= $pre_append.'{'.$value.'}';
							continue;
						}
						//$string .= 'foreach(loop';
					} else { // 기호가 없을때, 대입 또는 프린트 
						$prefix_value = '';
						$type = substr($fixed_value, 0, 1);
						if($type == '=') {
							$prefix_value = 'echo ';
							$fixed_value = substr($fixed_value, 1);
						}
						/* 따옴표 안의 주석구분문자 //를 이스케이프 */
						$fixed_value = preg_replace('#([\'"])(.*)//(.*)?([\'"])#Us', '$1$2\/\/$3$4', $fixed_value);
						/* 주석구분문자 이후의 문자열 삭제 */
						$fixed_value = preg_replace('#//(.*)#','', $fixed_value);
						/* 이스케이프 된 따옴표 안의 주석구분문자 //를 원래대로 되돌림 */
						$fixed_value = preg_replace('#([\'"])(.*)\\\/\\\/(.*)([\'"])#Us', '$1$2//$3$4', $fixed_value);
						/* =는 대입이므로 따옴표 안의 =를 이스케이프 */
						$fixed_value = preg_replace('#([\'"])(.*)=(.*)?([\'"])#Us','$1$2\&#61;$3$4',$fixed_value);
						/* = 를 잘라 변수형태로 분리 */
						preg_match_all('@(.*)\=@Us',$fixed_value, $plug_token);
						// 일반 문자열 이외의 문자열이 있으면 일반 문자열 취급. todo : parser error 유발
						if(preg_replace('#[[a-zA-Z][a-zA-Z0-9_\._\s]{0,}#','',implode("",$plug_token[1]))) {
							//echo $this->build_var($value);
							//pr($value);
							$string .= $pre_append.'{'.$value.'}';
							continue;
						}

						/* 할당 변수를 분리 */
						$fixed_value2 = str_replace(implode("",$plug_token[0]),'',$fixed_value);
						/*할당 변수 만들기*/
						$chk = 0;
						foreach($plug_token['1'] as $key2 => $value2) {
							$value3 = $this->build_var($value2);
							if(!$value3) {
								$chk = 1;
							}
							$prefix_value .= $value3.'=';
						}

						/* 이스케이프 된 따옴표 안의 =를 원래대로 되돌림 */
						$fixed_value2 = preg_replace('@([\'"])(.*)\\\&#61;(.*)([\'"])@Us', '$1$2=$3$4', $fixed_value2);
			//			$string .= $pre_append."<?php ".$prefix_value.'$'.$fixed_value2."? >";
						$value4 = $this->build_var($fixed_value2);
						if($value4 === '') {
							$this->report('Error #20', '예약 변수는 다중 배열일수 없음 error : line : '.$line.' '.$fixed_value,true,true);
							$this->exit_();
						} else if($chk || 0 === $value4) {
							$string .= $pre_append.'{'.$value.'}';
							continue;
	//						$this->report('Error #20', 'parser error : line : '.$line.' '.$fixed_value,true,true);
	//						$this->exit_();
						}
						$string .= $pre_append.'<?php '.$prefix_value."".''.$value4.";?>";
					}
				}
		//	pr($branch);
			}
			if($branch) {
				$f = array_shift($branch);
				$this->report('Error #9a', ' >'.$f[1].' line '.$f[0].' / template loop or branch is not properly closed by <b>{/}</b>', true);
				$this->exit_();
			}
		} else {
			$string = $source;
		}
		if (trim($this->postfilter)) $string=$this->_filter($string, 'post');
		return $string;
	}
	public function loop($loop_key, $loop_value) {
		$key = $this->get_word($loop_key);
		//return '<?php '.'if(true == is_array('.$loop_value.') {foreach('.$loop_value.' AS '.$key.') {{? >';
		return '<?php '.'$'.$key.'size_=count('.$loop_value.');if(true == is_array('.$loop_value.') && $'.$key.'size_ > 0) {'.'$i'.$key.'=-1;foreach('.$loop_value.' AS $k'.$key.' => '.$loop_key.') {$i'.$key.'++;$'.$key.'value_='.$loop_key.';$'.$key.'key_=$k'.$key.';$'.$key.'index_=$i'.$key.';if(($i'.$key.'+ 1) == $'.$key.'size_) {$'.$key.'last_=1;} else {$'.$key.'last_=0;}'.'?>';
	}
	/* .를 배열 형태로 변환 */
	public function build_var($key) {
		$enter = $this->fix_enter($key);
//		if (preg_match('@('.implode("|",$this->rsv_words).')$@', trim($key))) { // array.key_ , value_ , index_
//		}

		if(($a= ($this->_compile_expression($key)))) {
			return $enter.$a;
		} else {
			return $a;
		}
		if(($count = substr_count($key, '.'))) {
			$_tmp = explode('.', $key);
			$first = array_shift($_tmp);
			$tmp = '$'.$first."['".implode("']['",$_tmp)."']";
		} else {
			$tmp = '$'.$key;
		}	
		return $tmp;
	}
	/*string 중앙의 enter를 제일 마지막으로 보낸다.*/
	public function fix_enter($str) {
		return str_repeat(chr(10), substr_count($str, chr(10)));
	}
	public function get_word($str) {
		return preg_replace('#([^\w]+)#','_',$str);
	}
	private function _compile_expression($expression, $escape=0, $no_directive=0) {
		if (!strlen($expression)) return 0;
		$var_state			=array(0,'');					// 0:
		$par_stack			=array();
		$func_list			=array();
		$this->exp_object	=array();
		$this->exp_error	=array();
		$this->exp_loopvar	=array();
		$this->_outer_size	=array();
		$number_used		=0;
		$prev_is_operand	=0;
		$prev_is_func		=0;
		$m					=array();
		for ($xpr='',$i=0; strlen($expression); $expression=substr($expression, strlen($m[0])),$i++) {	// 
			if (!preg_match('/^
				((?:\.\s*)+)
				|(?:([A-Z_=a-z\x7f-\xff][\w\x7f-\xff\\\]+)\s*(\[|\.|\(|\-\>)?)
				|(?:(\])\s*(\-\>|\.|\[)?)
				|((?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+\-]?\d+)?)
				|('.$this->quoted_str.')
				|(=>|===|!==|\+\+|--|\+\.|<<|>>|<=|>=|==|!=|&&|\|\||[,+\-*\/%&^~|<>()!])
				|(\s+)
				|(.+)
			/ix', $expression, $m)) return 0; // june =>| 추가, \\\ 추

			if (!empty($m[10])) {	// (.+)
				return 0;
			} elseif ($m[1]) {		// ((?:\.\s*)+)         
				if ($prev_is_operand || $var_state[0]) return 0;
				$prev_is_operand = 1;
				$var_state=array(1,preg_replace('/\s+/','',$m[1]));
			} elseif ($m[2]) {		// ([A-Z_a-z\x7f-\xff][\w\x7f-\xff]*)
				if (empty($m[3])) $m[3]='';		// (\[|\.|\(|\-\>)

				if (!$var_state[1] && in_array($m[2], $this->rsv_words)) { // array.key_ , value_ , index_
					return '';
					pr($var_state);
					pr($m);
					pr($xpr);
					pr($expression);
				}

				switch ($m[3]) {
				case ''  :
					switch ($var_state[0]) {
					case 0:
						if ($prev_is_operand) return 0;
						$prev_is_operand = 1;
						if (in_array(strtolower($m[2]),$this->key_words) || $this->auto_constant && in_array($m[2], $this->constants)) {
							$xpr.= $m[2];
						} elseif ($m[2]==='this') {
							$xpr.= '$this';
						} elseif ($m[2]==='tpl_') {
							$xpr.= '$TPL_TPL';
						} elseif ($m[2][0]==='_') {
							if ($this->safe_mode) $this->exp_error[]=array(4, $m[2]);
							$xpr.= in_array($m[2], $this->auto_globals) ? '$'.$m[2] : '$GLOBALS["'.substr($m[2],1).'"]';
						} else {
							$xpr.= '$'.$m[2].'';
						}
						break;
					case 1:
						$xpr.=$this->_compile_array($var_state[1].$m[2], 'stop');
						break;
					case 2:
						$xpr.= $var_state[1]==='obj' ? $m[2] : '["'.$m[2].'"]';
						break;
					}
					$var_state=array(0,'');
					break;
				case '(' :
					if ($var_state[0]) {
						if ($var_state[1]!=='obj') return 0;
					} else {
						if ($no_directive) return 0;
						$func = strtolower($m[2]);
						if (in_array($func, $this->func_plugins)) {
							$func_list[$func] = $this->nl_cnt;
						} else {
							if ($this->safe_mode) in_array($func, $this->safe_mode_functions) or $this->exp_error[]=array(5, $m[2]);
							else in_array($func, $this->all_functions) or $this->exp_error[]=array(7, $m[2]);
						}
					}
					$prev_is_operand=0;
					$prev_is_func=1;
					$par_stack[]='f';
					$var_state=array(0,'');
					$xpr.=$m[2].'(';
					break;
				case '[' :
					switch ($var_state[0]) {
					case 0:
						if ($prev_is_operand) return 0;
						$xpr.=$this->_compile_array($m[2]).'[';
						break;
					case 1:
						$xpr.=$this->_compile_array($var_state[1].$m[2]).'[';
						break;
					case 2:
						$xpr.= $var_state[1]==='obj' ? $m[2].'[' : '["'.$m[2].'"][';
						break;
					}
					$par_stack[]='[';
					$prev_is_operand=0;
					$prev_is_func=0;
					$var_state=array(0, '');
					break;
				case '.' :
					switch ($var_state[0]) {
					case 0:
						if ($prev_is_operand) return 0;
						$prev_is_operand=1;
						$var_state=array(1, $m[2].'.');
						break;
					case 1:
						$xpr.=$this->_compile_array($var_state[1].$m[2]);
						$var_state=array(2, '');
						break;
					case 2:
						$xpr.= $var_state[1]==='obj' ? $m[2] : '["'.$m[2].'"]';
						break;
					}
					break;
				case '->':
					switch ($var_state[0]) {
					case 0:
						if ($prev_is_operand) return 0;
						$prev_is_operand = 1;
						if (in_array($m[2], $this->_loop_stack)) {
							$xpr .= '$TPL_V'.$this->_loop_info[$m[2]].'->';
							// need not check safe_mode.
						} elseif ($m[2]==='this') {
							if ($this->safe_mode) $this->exp_error[]=array(6, $m[2]);
							$xpr .= '$this->';
						} elseif ($m[2][0]==='_') {
							if ($this->safe_mode) $this->exp_error[]=array(4, $m[2]);
							$xpr .= '$GLOBALS["'.substr($m[2],1).'"]->';
						} else {
							//$xpr .= '$'.$m[2].'->';
							$xpr .= '$'.$m[2].'->';
						}
						break;
					case 1:
						$xpr.=$this->_compile_array($var_state[1].$m[2], 'obj').'->';
						break;
					case 2:
						$xpr.=($var_state[1]==='obj' ? $m[2] : '["'.$m[2].'"]').'->';
						break;
					}
					$var_state=array(2,'obj');
					break;
				}
			} elseif ($m[4]) {	//	(\])
				if ($var_state[0] || !$prev_is_operand || empty($par_stack) || array_pop($par_stack)!=='[') return 0;
				if (empty($m[5])) $m[5]='';
				switch ($m[5]) {
				case ''  :
					$xpr.=']';
					break;
				case '->':
					$xpr.=']->';
					$var_state=array(2,'obj');
					break;
				case '.' :
					$xpr.=']';
					$var_state=array(2,'');
					break;
				case '[' :
					$xpr.='][';
					$par_stack[]='[';
					$prev_is_operand=0;
					$prev_is_func=0;
					break;
				}
			} elseif ($m[6]||$m[6]==='0') {			// ((?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+\-]?\d+)?)
				if ($prev_is_operand) return 0;
				$xpr .= ' '.$m[6];
				$prev_is_operand = 1;
				$number_used = 1;
			} elseif ($m[7]) {
				if ($prev_is_operand||preg_match('/ [+\-]$/',$xpr)) return 0;
				$xpr=preg_replace('/\+$/','.',$xpr) . strtr($m[7],array('``'=>'`', '{`'=>'{', '`}'=>'}', '<?`'=>'<?', '`?>'=>'?>', '<%`'=>'<%', '`%>'=>'%>'));
				$prev_is_operand = 1;
			} elseif ($m[8]) {
				if ($var_state[0]) return 0;
				switch ($m[8]) {
				case'++':
				case'--':
					return 0;
				case ',':
					if (!$prev_is_operand || empty($par_stack) || $par_stack[count($par_stack)-1]!=='f') return 0;
					$prev_is_operand=0;
					break;
				case '(':
					if ($prev_is_operand) return 0;
					$par_stack[]='p';
					break;
				case ')':
					if (!$prev_is_operand && !$prev_is_func || empty($par_stack) || array_pop($par_stack)==='[') return 0;
					$prev_is_operand=1;
					break;
				case '!':
				case '~':
					if ($prev_is_operand) return 0;
					break;
				case '-':
					if ($prev_is_operand) $prev_is_operand=0;
					else $m[8]=' -';
					break;
				case '+':
					if (preg_match('/["\']$/', $xpr)) {
						$m[8]='.';
						$prev_is_operand=0;
					} else {
						if ($prev_is_operand) $prev_is_operand=0;
						else $m[8]=' +';
					}
					break;
				case '+.':
					$m[8]='.';
				default	:
					if (!$prev_is_operand) return 0;
					$prev_is_operand=0;
				}
				$xpr .= $m[8];
				$prev_is_func=0;
			} else {
				continue;
			}
		}
		if (!empty($par_stack) || !$prev_is_operand || $var_state[0] || $no_directive && $i===1 && $number_used) return 0;
		if ($escape) return 1;
		if (!empty($this->exp_error)) {
			foreach ($this->exp_error as $error) {
				switch ($error[0]) {
				case 1:
					$this->report('Error #20', '<b>p.</b> in <b>{'.$this->statement.'}</b> is reserved variable for accessing object plugins',true,true);
					$this->exit_();
					break;
				case 2:
					$this->report('Error #21', '<b>c.</b> in <b>{'.$this->statement.'}</b> is reserved variable for accessing constants',true,true);
					$this->exit_();
					break;
				case 3:
					$this->report('Warning #4', 'loop var <b>'.$error[1].'</b> in <b>{'.$this->statement.'}</b> is not in proper loop',true,true);
					break;
				case 4:
					$this->report('Error #22', 'safe mode : global variable <b>'.$error[1].'</b> in <b>{'.$this->statement.'}</b> is not available',true,true);
					$this->exit_();
				case 5:
					$this->report('Error #23', 'safe mode : function <b>'.$error[1].'()</b> in <b>{'.$this->statement.'}</b> is not registered',true,true);
					$this->exit_();
				case 6:
					$this->report('Error #24', 'safe mode : <b>this-></b> in <b>{'.$this->statement.'}</b> is not available',true,true);
					$this->exit_();
				case 7:
					$this->report('Error #25', 'call to undefined function <b>'.$error[1].'</b> in <b>{'.$this->statement.'}</b>',true,true);
					$this->exit_();
				case 8:
					$this->report('Error #26', 'cannot find plugin file for object <b>'.$error[1].'</b> in <b>{'.$this->statement.'}</b>',true,true);
					$this->exit_();
				}
			}
			return 0;
		}
		foreach ($this->_outer_size as $loop_id=>$depth) {
			if ($depth===1 && $this->in_div) $this->_size_info[$this->in_div][$loop_id] = 1;
			if (empty($this->_size_info[$depth][$loop_id])) {
				$this->_size_info[$depth][$loop_id] = array($this->statement, $this->nl_cnt);
			}
		}
		foreach ($this->exp_loopvar as $depth=>$set) {
			$this->_loop_info[$depth]['foreach_bit'] |= $set;
		}
		if ($func_list) {
			$this->_set_function($func_list);
			if ($this->in_div) $this->_set_function($func_list, $this->in_div);
		}
		if ($this->exp_object) {
			$this->_set_class($this->exp_object);
			if ($this->in_div) $this->_set_class($this->exp_object, $this->in_div);
		}
		return $xpr;
	}
	public function _compile_array($subject, $end='') {
		if (preg_match('/^\.+/', $subject, $match)) { // ..loop
			$depth=strlen($match[0]);
			if ($this->_loop_depth < $depth) {
				$this->exp_error[]=array(3, $subject);
				return '';
			}
			$id=$this->_loop_stack[$depth-1];
			$var=substr($subject, $depth);
			$el='["'.$var.'"]';
		} else {
			if ($D=strpos($subject,'.')) { // id.var
				$id=substr($subject,0,$D);
				$var=substr($subject,$D+1);
				$el='["'.$var.'"]';
				if ($id==='p1' || $id==='P1') { // p.object
					if (!$end) {
						$this->exp_error[]=array(1, $subject);
						return '';
					}
					$obj = strtolower($var);
					if (in_array($obj, $this->obj_plugins)) {
						$this->exp_object[$obj] = $this->nl_cnt;
					} else {
						$this->exp_error[]=array(8, $subject);
					}
					return '$TPL_'.$obj.'_OBJ';
				} elseif ($id==='c' || $id==='C') { // c.constant
					if ($end!=='stop') $this->exp_error[]=array(2, $subject);
					return $var;
				} elseif (in_array($id, $this->_loop_stack)) { // loop.var
					$depth=$this->_loop_info[$id];
				} elseif ($var==='asize_') { // outside.size_
					if ($end!=='stop') $this->exp_error[]=array(-1,$subject);
					$depth = $this->_loop_depth+1;
					$this->_outer_size[$id] = $depth;
					return '$TPL_'.$id.'_'.$depth;
				} elseif (in_array($var, $this->rsv_words)) { // array.key_ , value_ , index_

					$key = $this->get_word('$'.$id.'');
					return '$'.$key.$var;
					$this->exp_error[]=array(3, $subject);
					return '';
				}
			} else { // id[
				$id=$subject;
				$var='';
				$el='';
				if (in_array($id, $this->_loop_stack)) $depth=$this->_loop_info[$id];
			}
			if (empty($depth)) { // not loop
				if ($id[0]==='_') {
					if ($this->safe_mode) {
						$this->exp_error[]=array(4, $subject);
						return 0;
					}
					if (in_array($id, $this->auto_globals)) return '$'.$id.$el;
					return '$GLOBALS["'.substr($id,1).'"]'.$el;
				}
//				return '$'.$id.$el;
				return '$'.$id.''.$el;
			}
		}
		switch ($var) {
		case 'key_':
			if ($end!=='stop') $this->exp_error[]=array(-1,$subject);
			elseif (isset($this->exp_loopvar[$depth])) $this->exp_loopvar[$depth] |= 1;
			else $this->exp_loopvar[$depth] = 1;
			return '$TPL_K'.$depth;
		case 'value_':
			if (isset($this->exp_loopvar[$depth])) $this->exp_loopvar[$depth] |= 2;
			else $this->exp_loopvar[$depth] = 2;
			return '$TPL_V'.$depth;
		case 'index_a':
			if ($end!=='stop') $this->exp_error[]=array(-1,$subject);
			elseif (isset($this->exp_loopvar[$depth])) $this->exp_loopvar[$depth] |= 4;
			else $this->exp_loopvar[$depth] = 4;
			return '$TPL_I'.$depth;
		case 'size_a':
			if ($end!=='stop') $this->exp_error[]=array(-1,$subject);
			elseif (isset($this->exp_loopvar[$depth])) $this->exp_loopvar[$depth] |= 8;
			else $this->exp_loopvar[$depth] = 8;
			return $id==='*' ? '$TPL_S'.$depth : '$TPL_'.$id.'_'.$depth;
		default :
			return '$TPL_V'.$depth.$el;
		}
	}	
	public function report($type, $msg) {
		echo $type;
		echo ' ';
		echo $msg;
	}
	public function exit_() {
		exit();
	}	
}
