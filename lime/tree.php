<?php

namespace lime;

class treeException extends \Exception 
{ 
}

class tree_set_model 
{

	/* 새로운 노드 생성 */
	protected function _new_tree($name, $node) {	
		return \master::setid("
			INSERT INTO
				tree1
			SET
				parent_id		= :parent_id,
				element_name	= :element_name,
				left_node		= :left_node, 
				right_node		= :right_node
		", array(
			':parent_id'	=> (int)$this->get_parent_id_by_node($node), 
			':element_name'	=> $name,
			':left_node'	=> $node['left_target'], 
			':right_node'	=> $node['right_target']				
		));
	}

	/* src 노드를 이동시킨다 */
	protected function _move_node($src, $to) {
		$shift_size = $src['right_target']-$src['left_target']+1;
		$this->_update_first_delta( $to, $shift_size);
		if($src['left_target'] >= $to){ // src was shifted too?
			$src['left_target']		+= $shift_size;
			$src['right_target']	+= $shift_size;
		}
		/* now there's enough room next to target to move the subtree*/
		$newpos = $this->_update_first_last_delta( $src['left_target'], $src['right_target'], $to-$src['left_target']);
		/* correct values after source */
		$this->_update_first_delta( $src['right_target']+1, -$shift_size);
		if($src['left_target'] <= $to){ // dst was shifted too?
			$newpos['left_target']	-= $shift_size;
			$newpos['right_target']	-= $shift_size;
		} 

		$this->_update_parent_id($src['element_id'], $newpos);
		return $newpos;
	}

	/* first보다, 같거나 큰 left_node, right_node에 delta를 더해준다. delta는 음수도 가능 */
	protected function _update_first_delta($first, $delta) {
		$bind =  array(
			':first' => $first,
			':delta' => $delta	
		);
		\master::set("
			UPDATE 
				tree1
			SET
				left_node=left_node + :delta 
			WHERE
				left_node>=:first
		", $bind);
		\master::set("
			UPDATE
				tree1 
			SET 
				right_node=right_node + :delta
			WHERE
				right_node>=:first
		", $bind);
	}

	/* first보다, 같거나 코고 last보다 작거나 같은, left_node, right_node에 delta를 더해준다. delta는 음수도 가능, first, last에 delta가 더해진 배열이 리턴된다. */
	protected function _update_first_last_delta($first, $last, $delta) {
		$bind = array(
			':delta'	=> $delta,
			':first'	=> $first,
			':last'		=> $last	
		);

		\master::set("
			UPDATE
				tree1 
			SET
				left_node=left_node + :delta
			WHERE
				left_node>=:first 
			AND
				left_node<=:last
		", $bind);
		\master::set("
			UPDATE
				tree1
			SET
				right_node=right_node + :delta
			WHERE
				right_node>=:first
			AND
				right_node<=:last
		", $bind);

		return array(
			'left_target'	=> $first + $delta,
			'right_target'	=> $last  + $delta
		);
	}

	/* 이동후 parent id를 재설정 */
	protected function _update_parent_id($element_id, $node) {
		return \master::set("
			UPDATE 
				tree1
			SET
				parent_id = :parent_id
			WHERE
				element_id = :element_id
		", array(
			':element_id'	=> $element_id,
			':parent_id'	=> $this->get_parent_id_by_node($node)
		));	
	}

	protected function _delete_node ($node) {
		$leftanchor = $node['left_target'];
		$res = \master::set("
			DELETE FROM
				tree1
			WHERE 
				left_node>= :left_target
			AND 
				right_node<=:right_target
		", array(
			':left_target'	=> $node['left_target'],
			':right_target'	=> $node['right_target']
		));
		$this->_update_first_delta($node['right_target']+1, $node['left_target'] - $node['right_target'] -1);
		return $this->get_node(
					"left_node<".$leftanchor
			 ." ORDER BY left_node DESC"
		);
	}

	protected function _all_delete_tree () {
		return \master::set("DELETE FROM tree1");
	}
}

class tree_get_model extends tree_set_model
{

	/* get */
	protected function _get_tree($is_hierarchical = true) {
		$query = "
			SELECT 
		";
		if($this->table_name) {
			$query .= " extra.*, ";
		}
		$query .= "
				node.* ,
				(
				SELECT
					COUNT(*)  AS level 
				FROM
					tree1
				WHERE 
					left_node	< node.left_node
				AND
					right_node	> node.right_node ) AS depth
			FROM 
				tree1 AS node			
		";
		if($this->table_name) {
			$query .= "
				LEFT JOIN {$this->table_name} as extra
			ON
				node.element_id = extra.element_id
			";
		}
		$nodes = \master::gets($query, array(
//			':group_id' => $this->group_id	
		));

		usort($nodes, function ($a, $b) {
			if ($a['left_node'] == $b['left_node']) {
				return 0;
			}
			return ($a['left_node'] < $b['left_node']) ? -1 : 1;
		});
		if($is_hierarchical == true) {
			$nodes = $this->to_hierarchical_array($nodes);
		}
		return $nodes;
	}

	protected function _get_child($node, $is_hierarchical = true) {
		$query = "
			SELECT 
		";
		if($this->table_name) {
			$query .= " extra.*, ";
		}
		$query .= "
				node.* ,
				(
					SELECT
						COUNT(*)  AS level 
					FROM
						tree1
					WHERE 
						left_node	< node.left_node
					AND
						right_node	> node.right_node 
				) AS depth
			FROM 
				tree1 AS node
		";
		if($this->table_name) {
			$query .= "
				LEFT JOIN {$this->table_name} as extra
			ON
				node.element_id = extra.element_id
			";
		}
		$query .= "
			WHERE
				node.parent_id = :parent_id
			OR
				node.element_id = :parent_id
		";
		$nodes = \master::gets($query, array(
			':parent_id' => $node['element_id']	
		));
		usort($nodes, function ($a, $b) {
			if ($a['left_node'] == $b['left_node']) {
				return 0;
			}
			return ($a['left_node'] < $b['left_node']) ? -1 : 1;
		});
		if($is_hierarchical == true) {
			$nodes = $this->to_hierarchical_array($nodes);
		}
		return $nodes;
	}

	protected function _get_childs($node, $is_hierarchical = true) {
		$query = "
			SELECT 
		";
		if($this->table_name) {
			$query .= " extra.*, ";
		}
		$query .= "
				node.* ,
				(
					SELECT
						COUNT(*)  AS level 
					FROM
						tree1
					WHERE 
						left_node<node.left_node
					AND
						right_node>node.right_node 
				)  AS depth 
			FROM 
				tree1 AS node
		";
		if($this->table_name) {
			$query .= "
				LEFT JOIN {$this->table_name} as extra
			ON
				node.element_id = extra.element_id
			";
		}
		$query .= "
			WHERE
				node.left_node >=:left_node
			AND 
				node.right_node	<=:right_node
		";
		$nodes = \master::gets($query, array(
			':left_node'	=> $node['left_target'],	
			':right_node'	=> $node['right_target'],	
		));

		usort($nodes, function ($a, $b) {
			if ($a['left_node'] == $b['left_node']) {
				return 0;
			}
			return ($a['left_node'] < $b['left_node']) ? -1 : 1;
		});
		if($is_hierarchical == true) {
			$nodes = $this->to_hierarchical_array($nodes);
		}
		return $nodes;
	}

	/* element_id에 해당하는 path를 배열로 리턴 */
	public function get_path($element_id) {
		$nodes = \master::gets("
			SELECT
				parent.element_id,
				parent.element_name,
				parent.left_node
			FROM
				tree1 AS node
			INNER JOIN
				tree1 AS parent
			ON
				node.left_node
					BETWEEN 
						parent.left_node 
					AND
						parent.right_node
			WHERE 
				node.element_id = :element_id
		", array(
//			':group_id'		=> $this->group_id,
			':element_id'	=> $element_id,
		));
		usort($nodes, function ($a, $b) {
			if ($a['left_node'] == $b['left_node']) {
				return 0;
			}
			return ($a['left_node'] < $b['left_node']) ? -1 : 1;
		});
		return $nodes;
	}

	/* node의 parent id를 구함 */
	public function get_parent_id_by_node($node, $depth = 0) {
		return (int)\master::get1("
			SELECT
				element_id
			FROM
				tree1
			WHERE
				left_node < :left_node
			AND
				right_node > :right_node
			ORDER BY
				({$node['left_target']} - left_node)
			LIMIT
				{$depth}, 1
		", array(
//			':group_id'		=> $this->group_id,
			':left_node'	=> $node['left_target'],
			':right_node'	=> $node['right_target']
		));
	}

	function get_node($whereclause) {
		$noderes['element_id']		= 0;
		$noderes['left_target']		= 0;
		$noderes['right_target']	= 0;

		return \master::get("
			SELECT
				element_id,
				left_node AS left_target,
				right_node AS right_target
			FROM
				tree1
			WHERE 
				".$whereclause
		);
	}

	/* returns node level. (root level = 0)*/
	function get_level ($node) { 
		return (int)\master::get1("
			SELECT
				COUNT(*) AS level 
			FROM
				tree1
			WHERE 
				left_node<:left_node
			AND
				right_node>:right_node
		", array(
			':left_node'	=> $node['left_target'],	
			':right_node'	=> $node['right_target']
		));				 
	}

}

class tree_query_model extends tree_get_model
{

	function get_node_by_id($id) { 
		return $this->get_node( "element_id=".$id);
	}
	/* returns the node that matches the left value 'leftval'. */
	function get_node_by_left($leftval) { 
		return $this->get_node( "left_node=".$leftval);
	}
	function get_node_by_right($rightval) {
		return $this->get_node( "right_node=".$rightval);
	}
	function get_root () { 
		return $this->get_node("left_node=1");
	}
	function get_first_child ($node) {
		return $this->get_node("left_node=".($node['left_target']+1));
	}
	function get_last_child ($node) { 
		return $this->get_node("right_node=".($node['right_target']-1));
	}
	function get_prev_sibiling ($node) { 
		return $this->get_node("right_node=".($node['left_target']-1));
	}
	function get_next_sibiling ($node) { 
		return $this->get_node("left_node=".($node['right_target']+1));
	}
	function get_parent_by_node ($node) { 
		return $this->get_node(
				 "left_node<".($node['left_target'])
				 ." AND right_node>".($node['right_target'])
				 ." ORDER BY ".'right_node'
			 );
	}
	/* only checks, if L-value < R-value (does no db-query)*/
	function valid_node ($node) {
		return ($node['left_target'] < $node['right_target']);
	}
	function has_parent ($node) { 
		return $this->valid_node( $this->get_parent_by_node($node));
	}
	function has_prev_sibiling ($node) {
		return $this->valid_node( $this->get_prev_sibiling($node));
	}
	function has_next_sibiling ($node) {
		return $this->valid_node( $this->get_next_sibiling($node));
	}
	function has_children ($node) {
		return (($node['right_target']-$node['left_target'])>1);
	}
	function is_root ($node) {
		return ($node['left_target']==1);
	}
	function is_leaf ($node) {
		return (($node['right_target']-$node['left_target'])==1);
	}
	/* returns true, if 'node1' is a direct child or in the subtree of 'node2' */
	function is_child ($node1, $node2) {
		return (($node1['left_target']>$node2['left_target']) and ($node1['right_target']<$node2['right_target']));
	}
	function is_child_or_equal ($node1, $node2) { 
		return (($node1['left_target']>=$node2['left_target']) and ($node1['right_target']<=$node2['right_target']));
	}
	function is_equal ($node1, $node2) {
		return (($node1['left_target']==$node2['left_target']) and ($node1['right_target']==$node2['right_target']));
	}
	/* node의 자식수 */
	function get_count_children ($node) {
		return (($node['right_target']-$node['left_target']-1)/2);
	}
}

class tree_controller extends tree_query_model {

	/* 새로운 루트를 만들고 노드를 반환 */
	function create ($name) {
		try {
			if($this->get_root()) {
				throw new Exception('루트가 이미 존재합니다.');
			}
			$newnode['left_target']		= 1;
			$newnode['right_target']	= 2;
			$newnode['element_id']		= $this->_new_tree ($name, $newnode);
			return $newnode;
		} catch(\Exception $e) {
			throw new treeException($e);
		}
	}

	/* node 하위 첫번째 위치에 노드를 만들고 반환 */
	function prepend ($name, $node) {
		try {
			if ($this->valid_node($node)){
				$newnode['left_target']		= $node['left_target']+1;
				$newnode['right_target']	= $node['left_target']+2;
				$this->_update_first_delta($newnode['left_target'], 2);
				$newnode['element_id']		= $this->_new_tree ($name, $newnode);
				return $newnode;
			} else {
				throw new treeException("can't create prepend: no node");
			}
		} catch(\Exception $e) {
			throw new treeException($e);
		}
	}

	/* node 하위 마지막 위치에 노드를 만들고 반환 */
	function append ($name, $node) {
		try {
			if ($this->valid_node($node)){
				$newnode['left_target']		= $node['right_target'];
				$newnode['right_target']	= $node['right_target']+1;
				$this->_update_first_delta($newnode['left_target'], 2);
				$newnode['element_id']		= $this->_new_tree ($name, $newnode);
				return $newnode;
			} else {
				throw new treeException("can't create append: no node");
			}
		} catch(\Exception $e) {
			throw new treeException($e);
		}
	}

	/* node 앞 같은 level에 노드를 만들고 반환 */
	function before ($name, $node) {
		try {
			if ($this->valid_node($node)){
				$newnode['left_target']		= $node['left_target'];
				$newnode['right_target']	= $node['left_target']+1;
				$this->_update_first_delta($newnode['left_target'], 2);
				$newnode['element_id']		= $this->_new_tree ($name, $newnode);
				return $newnode;
			} else {
				throw new treeException("can't create before: no node");
			}
		} catch(\Exception $e) {
			throw new treeException($e);
		}
	}

	/* node 뒤 같은 level에 노드를 만들고 반환 */
	function after ($name, $node) {
		try {
			if ($this->valid_node($node)){
				$newnode['left_target']		= $node['right_target']+1;
				$newnode['right_target']	= $node['right_target']+2;
				$this->_update_first_delta($newnode['left_target'], 2);
				$newnode['element_id']		= $this->_new_tree ($name, $newnode);
				return $newnode;
			} else {
				throw new treeException("can't create after: no node");
			}
		} catch(\Exception $e) {
			throw new treeException($e);
		}
	}
	
	/* src와 src하위 node를 같은 level의 destination 뒤로 이동 */
	function move_after($src, $destination) {
		try {
			\master::begin();
			if ($this->valid_node($src) && $this->valid_node($destination)){
				$result = $this->_move_node($src, $destination['right_target']+1);
				\master::commit();
				return $result;
			} else {
				throw new treeException("can't move after: no src or dest");
			}
		} catch(\Exception $e) {
			\master::rollback();
			throw new treeException($e);
		}
	}

	/* src와 src하위 node를 같은 level의 destination 앞으로 이동 */
	function move_before($src, $destination) {
		try {
			\master::begin();
			if ($this->valid_node($src) && $this->valid_node($destination)){
				$result = $this->_move_node($src, $destination['left_target']);
				\master::commit();
				return $result;
			} else {
				throw new treeException("can't move before: no src or dest");
			}
		} catch(\Exception $e) {
			\master::rollback();
			throw new treeException($e);
		}
	}

	/* src와 src하위 node를 destination하위 첫번째 위치로 이동 */
	function move_prepend($src, $destination) {
		try {
			\master::begin();
			if ($this->valid_node($src) && $this->valid_node($destination)){
				$result = $this->_move_node($src, $destination['left_target']+1);
				\master::commit();
				return $result;
			} else {
				throw new treeException("can't prepend: no src or dest");
			}
		} catch(\Exception $e) {
			\master::rollback();
			throw new treeException($e);
		}
	}

	/* src와 src하위 node를 destination하위 마지막 위치로 이동 */
	function move_append($src, $destination) {
		try {
			\master::begin();
			if ($this->valid_node($src) && $this->valid_node($destination)){
				$result = $this->_move_node($src, $destination['right_target']);
				\master::commit();
			} else{
				\master::rollback();
				throw new treeException("can't append: no src or dest");
			}
		} catch(\Exception $e) {
			\master::rollback();
			throw new treeException($e);
		}
	}

	/* 루트를 포함한 전체 트리 삭제 */
	function all_delete_tree () {
		try {
			\master::begin();
			$result = $this->_all_delete_tree();	
			\master::commit();
			return $result;
		} catch(\Exception $e) {
			\master::rollback();
			throw new treeException($e);
		}
	}

	/* node를 포함한 하위 트리 삭제 */
	function delete_node ($node) {
		try {
			\master::begin();
			$result = $this->_delete_node($node);
			\master::commit();
			return $result;
		} catch(\Exception $e) {
			master::bollback();
			throw new treeException($e);
		}
	}

	function get_tree() {
		return $this->_get_tree();
	}
	function get_child($node) {
		return $this->_get_child($node);
	}

	function get_childs($node) {
		return $this->_get_childs($node);
	}
}

class tree_view extends tree_controller
{
	public $id_name = '';
	private function make_list($list) {
		$result = '';
		if(is_array($list) && count($list)) {
			$result .= '<ul>';
			foreach ($list as $item) {
				$result .= '<li id="'.$this->id_name.''.$item['element_id'].'"  rel="'.($item['parent_id'] == 0 ? 'root' : 'folder').'"><a href="#">'.$item['element_name'].'</a>';
				if (isset($item['children'])) {
					$result .= $this->make_list($item['children']);
				}
				$result .= '</li>';
			}
			$result .= '</ul>';
		}
		return $result;
	}
	public function to_html($nodes = array(), $name = '') {
		$this->id_name = $name ? $name : uniqid(rand()).'_';
		return $this->make_list($nodes);
		return '<style>div.aa ul ul > li {padding-left:15px;}</style><div class="aa">'.$this->make_list($nodes).'</div>';
	}
	public function to_hierarchical_array($list,$parent=0){
		$result = array();
		foreach($list as $key => $value){
			if( $value['parent_id']==$parent ){
				$value['children'] = $this->to_hierarchical_array($list,$value['element_id']);
				$result[$value['element_id']] = $value;
			}
		}
		return $result;
	}
}

class tree extends tree_view 
{
	public $group_id;
	public $table_name;

	public function set_group_id($group_id) {
		$this->group_id		= $group_id;
	}
	public function set_extend_table_name($table_name) {
		$this->table_name	= $table_name;
	}
	public function extend($arr, $table_name = '') {
		if($table_name) {
			$this->table_name= $table_name;
		}
		$keys				= array();
		$values				= array();
		$bind				= array();
		foreach($arr as $key => $value) {
			$keys[]			= $key;
			$values[]		= ':'.$key;
			$bind[':'.$key]	= $value;
		}
		$keys	= implode(',', $keys);
		$values	= implode(',', $values);

		$sql = "
			INSERT INTO 
				{$this->table_name}
			({$keys})
				VALUES
			({$values})
			ON DUPLICATE KEY UPDATE 
				url=VALUES(url)
		";
		return \master::set($sql, $bind);
	}
	public function update_element_id($old_element_id, $new_element_id) {
		return \master::set("
			UPDATE
				tree1
			SET
				element_id = :new_element_id
			WHERE
				element_id = :old_element_id
		", array(
			':old_element_id' => $old_element_id,
			':new_element_id' => $new_element_id
		));	
	}
	public function rebuild_tree($tree) {
		try {
			if(true === is_array($tree)) {
				$keys = [];
				foreach($tree as $key => $value) {
					if(true === is_numeric($key)) {
						$keys[] = $key;
					}
				}
				$tree_key = implode(",", $keys);
				\master::begin();
				$this->all_delete_tree();

				$new_id = [];
				foreach($tree as $key => $value) {
					if(0 === (int)$value['parent_id']) { // root
						$node = $this->create($value['element_name']);
						//$this->update_element_id($node['element_id'], $value['element_id']);
					} else {
						if(true === is_numeric($key)) { // update
							$node = $this->append($value['element_name'], $this->get_node_by_id($new_id[$value['parent_id']]));
							//$this->update_element_id($node['element_id'], $value['element_id']);						
						} else { // create
							$node = $this->append($value['element_name'], $this->get_node_by_id($new_id[$value['parent_id']]));
						}
					}
					$new_id[$value['element_id']] = $node['element_id'];
				}
				\master::commit();
			}
			return true;
		} catch(\Exception $e) {
			\master::rollback();
			throw new treeException($e);		
		}	
	}
/*
	array(
		'table' => 'tree1',
		'filed' => array(
		
		)
	);
*/
}

/*
		$extend		= function($node, $url) use(&$tree) {
			$field	= array(
				'element_id'	=> $node['element_id'],
				'url'			=> $url
			);
			$tree->extend($field, 'tree1_extend');
			return $node;
		};

		$tree		= new \lime\tree();

		$tree->all_delete_tree();
		$root		= $tree->create("root");

		$child3		= $extend($tree->append('PAGES', $root), 'pages');
		$child4		= $extend($tree->append('PRICING', $tree->get_root()), 'pricing');
		$child2		= $extend($tree->prepend('ABOUT US', $tree->get_root()), 'about');
		$child1		= $extend($tree->before('HOME', $child2), 'home');

		$child3_3	= $extend($tree->prepend('3.3', $tree->get_node_by_id($child3['element_id'])), '3-3');
		$child3_1	= $extend($tree->before('3.1', $child3_3), '3-1');
		$child3_2	= $extend($tree->after('3.2', $child3_1), '3-2');

		$child3_2_1 = $extend($tree->prepend("3.2.1", $child3_2), '3-2-1');
		$child		= $extend($tree->after("3.2.2", $child3_2_1), '3-2-2');


		$tree->move_after($tree->get_node_by_id($child3_2['element_id']), $tree->get_node_by_id($child2['element_id']));

		// pr($tree->getpath($child3_2['element_id']));

		$tree->move_before($tree->get_node_by_id($child3_2['element_id']), $tree->get_node_by_id($child3_3['element_id']));

		// pr($tree->getpath($child3_2['element_id']));
//		pr($tree->to_html($tree->get_child($tree->get_root())));
//		pr($tree->to_html($tree->get_childs($tree->get_node_by_id($child3['element_id']))));
//		pr($tree->get_path($child3['element_id']));

		// $tree->delete_node($tree->get_node_by_id($child3_2['element_id']));

		//$tree->nstPrintTree(array("element_name"));
		$this->set('tree', $tree->to_html($tree->get_tree(), 'tree_'));

*/