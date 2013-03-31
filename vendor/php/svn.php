<?php

namespace vendor\php;

require dirname(__file__).'/source/VersionControl/SVN.php';

class svn extends \VersionControl_SVN {
	public static $_auth = array();
	protected static $_svn;
    public static function svn() {
        if (null === self::$_svn) {
			// $svn = VersionControl_SVN::factory(__method__, $options);
            self::$_svn = \VersionControl_SVN::factory('__ALL__', self::$_auth);
        }
        return self::$_svn;
    }
	public function __construct($id, $pw) {
		self::_auth($id, $pw);
	}
	public static function _auth($id, $pw) {
		self::$_auth = array(
			'username'		=> $id
			, 'password'	=> $pw
			, 'fetchmode'	=> \VERSIONCONTROL_SVN_FETCHMODE_XML
			//, 'shortcuts'	=> array('ls' => 'List')
		);
	}
	public function exec($method, $commend, $option) {
		try {
			return self::svn()->{$method}->run($commend, $option);	
		} catch (\VersionControl_SVN_Exception $e) {
			print_r($e->getMessage());
		}
	}	
	/*
add: 파일과 디렉토리를 버전관리 대상에 넣습니다. 저장소에
추가하도록 스케쥴링 되며, 다음 커밋할 때, 추가됩니다.
사용법: add PATH...
	*/
	public function add($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
blame (praise, annotate, ann): 지정한 파일이나 URL의 내용의 수정내역을
각 라인별로 리비전과 작성자를 보여줍니다.
사용법: blame TARGET[@REV]...

  REV가 지정되면, 지정된 REV에서부터 찾아 보여줍니다.
*/
	public function blame($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
cat: 지정한 파일이나 URL의 내용을 출력합니다.
사용법: cat TARGET[@REV]...

  REV가 지정되면, 지정된 REV에서부터 찾아 출력합니다.
*/
	public function cat($source, $revision_no = 'HEAD') {
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}	
/*
checkout (co): 작업사본을 저장소로부터 꺼냅니다.
사용법: checkout URL[@REV]... [PATH]

  REV가 지정되면, 지정된 REV에서부터 찾아 체크아웃합니다.
  
  PATH가 생략되면, URL의 맨마지막 디렉토리명이 꺼내어 저장될 디렉토리
  이름으로 사용됩니다. 만약, 여러개의 URL이 지정되면 PATH의 하위 디렉토리에
  저장됩니다. 이때는 각 URL의 맨 마지막 디렉토리명이 하위 디렉토리 이름으로
  사용됩니다.
  
  현 작업 사본에 관리대상으로 추가되지 않은 파일이 존재하고, checkout 할
  URL에 같은 이름의 파일이 관리대상으로 이미 존재하는 경우 오류를 내게 됩니다.
  이 경우 --force 를 주게되면, 오류가 나지 않으며, 저장소의 관리대상으로
  취급하게 됩니다. 두 대응되는 대상이 같은 형식(파일 또는 디렉토리)이면,
  파일의 경우 저장소의 내용과 다른 것이 작업사본에 있는 경우 저장소에서 
  꺼내온 뒤 수정한 것으로 봅니다. 즉, 현 작업 사본의 내용이 바뀌지
  않은 채 저장소의 메타 정보가 추가되며, 내용은 수정된 것으로 취급합니다.
  디렉토리의 경우 하위의 모든 파일이 버전 관리 대상으로 추가됩니다.
  또한, 저장소에 있는 대상의 속성들이 모두 작업사본에 적용됩니다.

  일어난 상황에 대한 상태를 파악하기 위해서
  'svn help update'를 실행합니다.
*/
	public function checkout($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
commit (ci): 변경된 내용을 작업 사본에서 저장소로 전송합니다.
사용법: commit [PATH...]
*/
	public function commit($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}

/*
copy (cp): 작업 사본 혹은 저장소의 내용을 이전 로그메시지와 함께 복사합니다.

사용법: copy SRC[@REV]... DST

여러개를 복사할 때, 이들은 모두 DST의 하위에 추가되며,
이때, DST는 반드시 디렉토리어야 합니다.

  SRC와 DST는 작업 사본(WC) 혹은 저장소 URL이 될 수 있습니다:
    WC  -> WC:   바로 복사하고 저장소에 이전 로그와 함께 추가하도록 스케쥴
    WC  -> URL:  작업사본을 URL에 복사하고 바로 커밋함
    URL -> WC:   URL로부터 체크아웃해서 현 작업 사본에 추가하도록 스케쥴
    URL -> URL:  서버상에서 바로 복사함; 브랜치,태그를 만들 때 사용됨
  모든 SRC들은 같은 종류의 것이어야 합니다.

경고: Subversion 이전 버전과의 호환성을 위해서, 작업 사본간의 복사
(WC -> WC)는 저장소를 경유하지 않고 수행됩니다. 이런 이유로 원본에서
사본으로 복사할 때, 병합되는 정보는 기본 동작으로는 전달되지 않습니다
*/
	public function copy($source, $target, $revision_no = 'HEAD', $message = '') {
		$options = array(
			'm' => $message,
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source, $target), $options);	
	}
/*
delete (del, remove, rm): 파일과 디렉토리를 버전 관리 대상에서 제거합니다.
사용법: 1. delete PATH...
        2. delete URL...

  1. PATH에 있는 모든 파일, 디렉토리들을 다음 커밋에서 제거하도록
     스케쥴합니다. 커밋되지 않은 파일과 디렉토리는 --keep-local 옵션을
     사용하지 않는한 즉시 작업사본에서 제거됩니다.
     PATH가 버전 관리 대상이 아니거나 그러한 파일을 포함하는 디렉토리라면
     --force 옵션을 주어야만 삭제됩니다.

  2. URL로 지정된 아이템들은 저장소에서 즉시 삭제됩니다.

*/
	public function delete($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}

/*
diff (di): 두 리비전 혹은 두 경로상의 차이점을 출력합니다.
사용법: 1. diff [-c M | -r N[:M]] [TARGET[@REV]...]
       2. diff [-r N[:M]] --old=OLD-TGT[@OLDREV] [--new=NEW-TGT[@NEWREV]] \
               [PATH...]
       3. diff OLD-URL[@OLDREV] NEW-URL[@NEWREV]

  1. REV 리비전에 있는 TARGET들이 N,M 두 리비전에서의 어떤 내용 변경이 있는지
     보여줍니다. TARGET들은 모두가 작업사본 경로이거나 모두가 URL일 수 있습니다.

     TARGET 중에 작업사본내의 경로이면서 N이 생략되면 BASE가 사용되고,
     경로가 아니면 N은 반드시 명시되어야합니다. 또한 작업사본내의 경로이면서,
     M이 생략되면 작업중인 파일이 사용되고, URL의 경우 HEAD가 사용됩니다.
     '-c M' 옵션은 N = M-1 인 '-r N:M' 옵션과 같습니다.
     '-c -M' 은 반대로 수행됩니다. 즉 '-r M:N, (N=M-1)'로 수행합니다.

  2. OLDREV 리비전의 OLD-TGT와 NEWREV 리비전의  NEW-TGT의 차이를 보여줍니다.
     PATH가 주어지면, OLD-TGT와 NEW-TGT에 대한 상대 경로를 구하여 차이를 보여주며,
     출력은 그 경로에 대한 것으로 제한됩니다.
     OLD-TGT와 NEW-TGT는 모두 작업 사본내의 경로나 URL[@REV]로 표현될 수 있습니다.
     NEW-TGT가 생략되면 OLD-TGT를 사용합니다. -r N은 OLD-TGT의 디폴트 리비전을 N으로
     -r N:M 은 OLDREV의 디폴트 리비전을 N으로 NEW-TGT에 대해선 M으로 처리합니다.
     -c M 은 OLDREV의 기본값을 M-1로 NEWREV의 기본값을 M으로 처리합니다.

  3. 'svn diff --old=OLD-URL[@OLDREV] --new=NEW-URL[@NEWREV]' 의 줄임 표현입니다.

  'svn diff' 을 사용하면 작업 중 수정된 내용을 볼 수 있습니다.
*/
	public function diff($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
export: 버전 정보 없는 깨끗한 사본을 받아옵니다.
usage: 1. export [-r REV] URL[@PEGREV] [PATH]
       2. export [-r REV] PATH1[@PEGREV] [PATH2]

  1. 리비전 REV에 해당하는 URL 의 내용을 PATH에 받아 옵니다.
     만약 REV가 지정되지 않으면 HEAD(최신 리비전)를 받아 옵니다.
     PATH가 생략되면 URL의 마지막 요소를 받아올 디렉토리 이름으로
     사용합니다.

  2. 리비전 REV에 해당하는 PATH1으로 지정된 작업 사본의 내용을 
     PATH2에 받아옵니다. REV 가 생략되면 현재 작업중인 내용을 그대로
     받아 옵니다. PATH2가 생략되면 PATH1의 마지막 요소를 디렉토리
     이름으로 사용합니다. REV가 생략될 경우 작업 중에 변경된 내용은 그대로
     유지되며, 버전 관리 대상이 아닌 파일들은 복사되지 않습니다.
     
  PEGREV가 지정될 경우엔, 어떤 리비젼에서 대상을 먼저 찾을지 결정합니다.

*/
	public function export($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
import: 버전관리 대상이 아닌 파일과 디렉토리를 추가합니다.
사용법: import [PATH] URL

  PATH의 하위 디렉토리를 재귀적으로 URL에 추가합니다.
  PATH가 생략되면 '.' 이 사용됩니다. 필요한 경우,
  저장소 상에 상위 디렉토리가 자동으로 생성됩니다.
  PATH가 디렉토리이면, 그 내용은 URL에 모두 추가 됩니다.
  --force가 지정되면 버전관리 할 수 없는 장치 파일이나
  파이프등은 무시됩니다

*/
	public function import($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
info: 작업 사본이나 저장소의 파일, 디렉토리의 정보를 출력합니다.
usage: info [TARGET[@REV]...]

  각 TARGET에 해당하는 정보를 출력합니다. (기본값: '.').
  TARGET은 작업 사본 경로 혹은 URL이 될 수 있습니다.  특정 리비전이후에
 변경된 내용을 확인하려면, REV를 지정하세요


*/
	public function info($source, $revision_no = 'HEAD') {
		ob_start();

		$options = array(
			'r' => $revision_no
		);
		$xml = $this->exec(__function__,array($source), $options);	
		if($xml) {
			$array = xml2array($xml);
			$r = array();
			if(isset($array['entry']) && is_array($array['entry'])) {
				foreach($array['entry'] as $k=>$v) {
					if($k == '@attributes') {
						foreach($v as $k2 => $v2) {
							if($k2 == '@attributes') {
							} else {
								$r[$k2] = $v2;
							}
						}			
					} else if($k == 'commit') {
						foreach($v as $k2 => $v2) {
							if($k2 == '@attributes') {
								foreach($v2 as $k3 => $v3) {
									$r[$k][$k3] = $v3;
								}			
							} else if($k2 == 'date') {
								$r[$k][$k2] = date('Y-m-d H:i:s', strtotime($v2));					
							} else {
								$r[$k][$k2] = $v2;
							}
						}
					} else {
						$r[$k] = $v;
					}
				}
			} else {
				//pr($array['info']);
			}
		} else {
			$r = null;
		}

		$chk = ob_get_contents();
		@ob_end_clean();

		$this->flog('$svn->info("'.$source.'", "'.$revision_no.'")'); 

		if($chk) {
			return array(
				'error' => $chk	
			);
		} else {
			return $r;
		}
	}
/*
list (ls): 저장소에 있는 항목들을 나열해줍니다.
사용법: list [TARGET[@REV]...]

  TARGET 파일과 TARGET 디렉토리의 내용을 저장소에 있는 내용대로
  출력해 줍니다. TARGET이 만일 작업 사본의 경로에 있는 것이라면
  해당하는 저장소의 URL이 사용됩니다.

  TARGET이 생략될 경우 현재 작업 사본의 URL이 사용됩니다.
  

  --verbose 가 사용되면, 다음 필드들이 보여지게 됩니다.

    마지막 커밋된 리비전 번호
    마지막 커밋한 작성자
    잠긴 파일에 대해서는 'O' 문자 ('svn info URL'로 조회)
    크기(바이트)
    마지막 커밋한 날짜와 시각
*/
	public function ls($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		//	, 'v' => true
			, 'xml' => false
			, 'R' => true
		);
		return $this->exec('list', array($source), $options);	
	}

	public function flog($source) {
		file_put_contents(DATA_FOLDER.'svn_log_'.date('Y-m-d').'.txt',"\n".'['.date('Y-m-d H:i:s').'] '.$source,  FILE_APPEND | LOCK_EX); 
	}
/*
log: Show the log messages for a set of revision(s) and/or file(s).
usage: 1. log [PATH]
       2. log URL[@REV] [PATH...]

  1. Print the log messages for a local PATH (default: '.').
     The default revision range is BASE:1.

  2. Print the log messages for the PATHs (default: '.') under URL.
     If specified, REV determines in which revision the URL is first
     looked up, and the default revision range is REV:1; otherwise,
     the URL is looked up in HEAD, and the default revision range is
     HEAD:1.

  Multiple '-c' or '-r' options may be specified (but not a
  combination of '-c' and '-r' options), and mixing of forward and
  reverse ranges is allowed.

  With -v, also print all affected paths with each log message.
  With -q, don't print the log message body itself (note that this is
  compatible with -v).

  Each log message is printed just once, even if more than one of the
  affected paths for that revision were explicitly requested.  Logs
  follow copy history by default.  Use --stop-on-copy to disable this
  behavior, which can be useful for determining branchpoints.

  Examples:
    svn log
    svn log foo.c
    svn log http://www.example.com/repo/project/foo.c
    svn log http://www.example.com/repo/project foo.c bar.c

*/
	public function log($source, $revision_no = '', $limit=0) {
		if($revision_no == 'first') {
			return $this->_log($source, '1:HEAD', 1); 
		} else if($revision_no == 'head') {
			return $this->_log($source, 'HEAD', 1); 
		} else {
			return $this->_log($source, $revision_no, $limit); 
		}
	}
	public function _log($source, $revision_no = '', $limit=0) {
		$options = array(
			'v' => true				
		);
		if($revision_no) {
			$options['r'] = $revision_no;
		}
		if($limit) {
			$options['l'] = $limit;
		}		
		$xml = $this->exec('log', array($source), $options);	
        $xml_entries = new \SimpleXMLElement($xml);

        $entries = array();        
		foreach($xml_entries->children() as $key => $xml_entry){
			$key = (string)$xml_entry['revision'];
            $entries[$key]['revision']	= (string)$xml_entry['revision'];
            $entries[$key]['date']		= date('Y-m-d H:i:s', strtotime((string)$xml_entry->date));
            $entries[$key]['msg']		= (string)$xml_entry->msg;
            $entries[$key]['author']	= (string)$xml_entry->author;

            foreach($xml_entry->paths as $paths) {
                $path_entries = array();
				foreach($paths as $path) {
					$path_entries[] = array(
						'kind'			=> (string)$path['kind']
						, 'action'		=> (string)$path['action']
						, 'path'		=> (string)$path
					);
			   }
            }
            $entries[$key]['paths']	= $path_entries;
        }
		$array = ( $entries);

		ksort($array);

		$this->flog('$svn->log("'.$source.'", "'.$revision_no.'", '.$limit.')'); 

		return $array;
	}
/*
merge: Apply the differences between two sources to a working copy path.
usage: 1. merge sourceURL1[@N] sourceURL2[@M] [WCPATH]
       2. merge sourceWCPATH1@N sourceWCPATH2@M [WCPATH]
       3. merge [-c M[,N...] | -r N:M ...] SOURCE[@REV] [WCPATH]

  1. In the first form, the source URLs are specified at revisions
     N and M.  These are the two sources to be compared.  The revisions
     default to HEAD if omitted.

  2. In the second form, the URLs corresponding to the source working
     copy paths define the sources to be compared.  The revisions must
     be specified.

  3. In the third form, SOURCE can be either a URL or a working copy
     path (in which case its corresponding URL is used).  SOURCE (in
     revision REV) is compared as it existed between revisions N and M
     for each revision range provided.  If REV is not specified, HEAD
     is assumed.  '-c M' is equivalent to '-r <M-1>:M', and '-c -M'
     does the reverse: '-r M:<M-1>'.  If no revision ranges are
     specified, the default range of 0:REV is used.  Multiple '-c'
     and/or '-r' options may be specified, and mixing of forward
     and reverse ranges is allowed.

  WCPATH is the working copy path that will receive the changes.
  If WCPATH is omitted, a default value of '.' is assumed, unless
  the sources have identical basenames that match a file within '.':
  in which case, the differences will be applied to that file.

  NOTE:  Subversion will only record metadata to track the merge
  if the two sources are on the same line of history -- if the
  first source is an ancestor of the second, or vice-versa.  This is
  guaranteed to be the case when using the third form listed above.
  The --ignore-ancestry option overrides this, forcing Subversion to
  regard the sources as unrelated and not to track the merge.
*/
	public function merge($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
mkdir: 디렉토리를 만들어 버전관리 대상으로 둡니다.
사용법: 1. mkdir PATH...
        2. mkdir URL...

  디렉토리를 만들어 버전관리 대상으로 만듭니다.

  1. PATH로 지정된 디렉토리들을 작업 디렉토리안에 만들고,
    다음 커밋할 때 추가되도록 스케쥴링 합니다.

  2. 각 디렉토리들을 지정한 URL에 직접 커밋되는 형식으로 생성합니다.
   

  두 경우 생성될 디렉토리 상위의 디렉토리들은 존재해야만 합니다.
  --parents 옵션을 주면, 중간 디렉토리를 생성합니다


*/
	public function mkdir($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
move (mv, rename, ren): 작업 사본내 혹은 저장소 안의 파일이나 디렉토리를 이동하거나, 이름을 바꿉니다.
사용법: move SRC DST

여러개를 SRC에 지정하여 옮길 때, 각각은 DST의 하위에 추가 되며,
DST는 디렉토리여야 합니다.

  주의: 이 명령은 'copy' 후 'delete'한 것과 동일합니다.
  주의: --revision 옵션은 더 이상 사용되지 않습니다.

  SRC와 DST 는 둘다 작업사본 혹은 URL이어야 합니다.
    WC  -> WC:   작업 사본내에서 바로 이동 후, 추가하도록 스케쥴링하며, 로그 메시지도 복사됩니다.
    URL -> URL:  서버 상에서만 이름을 바꾸며, 바로 커밋됩니다.
  모든 SRC들은 같은 종류의 것이어야 합니다.

*/
	public function move($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
revert: 작업 사본을 받아 왔던 최초 상태로 되돌립니다. (작업한 내용을 모두 되돌립니다.)
사용법: revert PATH...

  주의: 본 부속 명령은 네트워크 요청이 전혀 필요하지 않습니다. 또한 충돌한 상황을
  해결하지 않습니다. 그리고, 삭제된 디렉토리에 대해서는 다시 되돌려놓지 않습니다.
*/
	public function revert($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
status (stat, st): Print the status of working copy files and directories.
usage: status [PATH...]

  With no args, print only locally modified items (no network access).
  With -q, print only summary information about locally modified items.
  With -u, add working revision and server out-of-date information.
  With -v, print full revision information on every item.

  The first seven columns in the output are each one character wide:
    First column: Says if item was added, deleted, or otherwise changed
      ' ' no modifications
      'A' Added
      'C' Conflicted
      'D' Deleted
      'I' Ignored
      'M' Modified
      'R' Replaced
      'X' an unversioned directory created by an externals definition
      '?' item is not under version control
      '!' item is missing (removed by non-svn command) or incomplete
      '~' versioned item obstructed by some item of a different kind
    Second column: Modifications of a file's or directory's properties
      ' ' no modifications
      'C' Conflicted
      'M' Modified
    Third column: Whether the working copy directory is locked
      ' ' not locked
      'L' locked
    Fourth column: Scheduled commit will contain addition-with-history
      ' ' no history scheduled with commit
      '+' history scheduled with commit
    Fifth column: Whether the item is switched or a file external
      ' ' normal
      'S' the item has a Switched URL relative to the parent
      'X' a versioned file created by an eXternals definition
    Sixth column: Repository lock token
      (without -u)
      ' ' no lock token
      'K' lock token present
      (with -u)
      ' ' not locked in repository, no lock token
      'K' locked in repository, lock toKen present
      'O' locked in repository, lock token in some Other working copy
      'T' locked in repository, lock token present but sTolen
      'B' not locked in repository, lock token present but Broken
    Seventh column: Whether the item is the victim of a tree conflict
      ' ' normal
      'C' tree-Conflicted
    If the item is a tree conflict victim, an additional line is printed
    after the item's status line, explaining the nature of the conflict.

  The out-of-date information appears in the ninth column (with -u):
      '*' a newer revision exists on the server
      ' ' the working copy is up to date

  Remaining fields are variable width and delimited by spaces:
    The working revision (with -u or -v)
    The last committed revision and last committed author (with -v)
    The working copy path is always the final field, so it can
      include spaces.

  Example output:
    svn status wc
     M     wc/bar.c
    A  +   wc/qax.c

    svn status -u wc
     M           965    wc/bar.c
           *     965    wc/foo.c
    A  +         965    wc/qax.c
    Status against revision:   981

    svn status --show-updates --verbose wc
     M           965       938 kfogel       wc/bar.c
           *     965       922 sussman      wc/foo.c
    A  +         965       687 joe          wc/qax.c
                 965       687 joe          wc/zig.c
    Status against revision:   981

    svn status
     M      wc/bar.c
    !     C wc/qaz.c
          >   local missing, incoming edit upon update
    D       wc/qax.c
*/
	public function status($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
switch (sw): Update the working copy to a different URL.
usage: 1. switch URL[@PEGREV] [PATH]
       2. switch --relocate FROM TO [PATH...]

  1. Update the working copy to mirror a new URL within the repository.
     This behavior is similar to 'svn update', and is the way to
     move a working copy to a branch or tag within the same repository.
     If specified, PEGREV determines in which revision the target is first
     looked up.

     If --force is used, unversioned obstructing paths in the working
     copy do not automatically cause a failure if the switch attempts to
     add the same path.  If the obstructing path is the same type (file
     or directory) as the corresponding path in the repository it becomes
     versioned but its contents are left 'as-is' in the working copy.
     This means that an obstructing directory's unversioned children may
     also obstruct and become versioned.  For files, any content differences
     between the obstruction and the repository are treated like a local
     modification to the working copy.  All properties from the repository
     are applied to the obstructing path.

     Use the --set-depth option to set a new working copy depth on the
     targets of this operation.

  2. Rewrite working copy URL metadata to reflect a syntactic change only.
     This is used when repository's root URL changes (such as a scheme
     or hostname change) but your working copy still reflects the same
     directory within the same repository.

  See also 'svn help update' for a list of possible characters
  reporting the action taken.
*/
	public function swich($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
/*
update (up): Bring changes from the repository into the working copy.
usage: update [PATH...]

  If no revision is given, bring working copy up-to-date with HEAD rev.
  Else synchronize working copy to revision given by -r.

  For each updated item a line will start with a character reporting the
  action taken.  These characters have the following meaning:

    A  Added
    D  Deleted
    U  Updated
    C  Conflict
    G  Merged
    E  Existed

  A character in the first column signifies an update to the actual file,
  while updates to the file's properties are shown in the second column.
  A 'B' in the third column signifies that the lock for the file has
  been broken or stolen.

  If --force is used, unversioned obstructing paths in the working
  copy do not automatically cause a failure if the update attempts to
  add the same path.  If the obstructing path is the same type (file
  or directory) as the corresponding path in the repository it becomes
  versioned but its contents are left 'as-is' in the working copy.
  This means that an obstructing directory's unversioned children may
  also obstruct and become versioned.  For files, any content differences
  between the obstruction and the repository are treated like a local
  modification to the working copy.  All properties from the repository
  are applied to the obstructing path.  Obstructing paths are reported
  in the first column with code 'E'.

  Use the --set-depth option to set a new working copy depth on the
  targets of this operation.


*/
	public function update($source, $revision_no = 'HEAD') { // todo
		$options = array(
			'r' => $revision_no
		);
		return $this->exec(__function__, array($source), $options);	
	}
}
function xml2array($xml){  
	$data	= simplexml_load_string($xml); 
	$json	= json_encode($data); 
	$result	= json_decode($json,true); 
	return $result; 
}
