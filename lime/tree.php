<?php

namespace lime;

class treeException extends \Exception 
{ 
}

class tree_model {

	/* 
		creates a new root record and returns the node 'left_target'=1, 'right_target'=2.
		새로운 루트 레코드를 left 1, right 2로 생성하고 sequence를 반환
	*/
	function _new_tree($name, $node) {
		try {
			$node['parent_id'] = $this->get_parent_id_by_node($node);
			return \master::setid("
				INSERT INTO
					tree1
				SET
					parent_id		= :parent_id,
					element_name	= :element_name,
					left_node		= :left_node, 
					right_node		= :right_node
			", array(
				':parent_id'	=> $node['parent_id'], 
				':element_name'	=> $name,
				':left_node'	=> $node['left_target'], 
				':right_node'	=> $node['right_target']				
			));
		} catch(Exception $e) {
			throw new treeException($e);
		}
	}

	/* '$src' is the node/subtree, '$to' is its destination l-value 
		src 노드를 left가 to인 위치로 이동시긴다
	*/
	function _move_node($src, $to) {
		try {
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

		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
		return $newpos;
	}


	/* 
		first보다, 같거나 큰 left_node, right_node에 delta를 더해준다. delta는 음수도 가능
	 */
	function _update_first_delta($first, $delta) {
		try {
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
		} catch(Exception $e) {
			throw new treeException($e);
		}
	}

	/* 
		first보다, 같거나 코고 last보다 작거나 같은, left_node, right_node에 delta를 더해준다. delta는 음수도 가능
		first, last에 delta가 더해진 배열이 리턴된다.
	 */
	function _update_first_last_delta($first, $last, $delta) {
		try {
			$bind = array(
				':delta'	=> $delta,
				':first'	=> $first,
				':last'		=> $last	
			);

			\master::set("
				UPDATE
					tree1 
				SET
					left_node=left_node+ :delta
				WHERE
					left_node>=:first 
				AND
					left_node<=:last
			", $bind);
			\master::set("
				UPDATE
					tree1
				SET
					right_node=right_node+ :delta
				WHERE
					right_node>=:first
				AND
					right_node<=:last
			", $bind);

			return array(
				'left_target'	=> $first + $delta,
				'right_target'	=> $last  + $delta
			);
		} catch(Exception $e) {
			throw new treeException($e);
		}
	}

	/*이동후 parent id를 재설정*/
	function _update_parent_id($element_id, $node) {
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
	
	function _get_tree($depth = null, $mode = 'include') {

		$query = "
			SELECT 
				a.* ,
				(
				SELECT
					count(*)  AS level 
				FROM
					tree1
				WHERE 
					left_node<a.left_node
				AND
					right_node>a.right_node ) AS depth
			FROM 
				tree1 as a				
		";

		$nodes = \master::gets($query, array(
//			':group_id' => $this->group_id	
		));

		usort($nodes, function ($a, $b) {
			if ($a['left_node'] == $b['left_node']) {
				return 0;
			}
			return ($a['left_node'] < $b['left_node']) ? -1 : 1;
		});

		return $nodes;
	}

	public function get_child($node) {
		$query = "
		SELECT 
			node.* ,
			(
				SELECT
					count(*)  AS level 
				FROM
					tree1
				WHERE 
					left_node<node.left_node
				AND
					right_node>node.right_node 
			) AS depth
		FROM 
			tree1 as node
		WHERE
			node.parent_id = :parent_id
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
		return $nodes;
	}


	public function get_childs($node) {
		$query = "
		SELECT 
			node.* ,
			(
				SELECT
					count(*)  AS level 
				FROM
					tree1
				WHERE 
					left_node<node.left_node
				AND
					right_node>node.right_node 
			) AS depth
		FROM 
			tree1 as node
		WHERE
			node.left_node		>:left_node
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
		return $this->toArray($nodes);
	}

	/**
	 * Get path of an element
	 *
	 * @param $element_id|int	Id of the element we want the path of
	 *
	 * @return array
	 */
	public function get_path_by_id($element_id) {
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

	/**
	 * Get the parent of an element.
	 *
	 * @param $element_id|int	Element ID
	 * @param $depth|int		Depth of the parent, compared to the child.
	 *							Default is 1 (as immediate)
	 *
	 * @return array|false
	 */

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

	function _delete_node ($node) {
		$leftanchor = $node['left_target'];
		try {
			$res = \master::set("
				DELETE FROM
					tree1
				WHERE 
					left_node>=".$node['left_target']."
				AND 
					right_node<=".$node['right_target']
			);
			$this->_update_first_delta($node['right_target']+1, $node['left_target'] - $node['right_target'] -1);
			return $this->get_node(
						"left_node<".$leftanchor
				 ." ORDER BY left_node DESC"
			 );
		} catch(\Exception $e) {
			throw new treeException($e);
		}
	}

	function _all_delete_tree () {
		try {
			return \master::set("DELETE FROM tree1");
		} catch(\Exception $e) {
			throw new treeException($e);
		}
	}
	/* returns the first node that matches the '$whereclause'. 
		 The WHERE-caluse can optionally contain ORDER BY or LIMIT clauses too. 
	 */
	function get_node($whereclause) {
		$noderes['element_id']		= 0;
		$noderes['left_target']		= 0;
		$noderes['right_target']	= 0;
		try {
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
		} catch(\Exception $e) {
			throw new treeException($e);
		}
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


class tree_controller extends tree_model {

	/* ******************************************************************* */
	/* Tree Constructors */
	/* ******************************************************************* */

	/* creates a new root record and returns the node 'left_target'=1, 'right_target'=2. */
	function create ($name) {
		if($this->get_root()) {
			throw new Exception('only one root');
		}	

		$newnode['left_target']		= 1;
		$newnode['right_target']	= 2;
		$newnode['element_id']		= $this->_new_tree ($name, $newnode);
		return $newnode;
	}

	/* creates a new first child of 'node'. */
	function prepend ($name, $node) {
		$newnode['left_target']		= $node['left_target']+1;
		$newnode['right_target']	= $node['left_target']+2;
		$this->_update_first_delta($newnode['left_target'], 2);
		$newnode['element_id']		= $this->_new_tree ($name, $newnode);
		return $newnode;
	}

	/* creates a new last child of 'node'. */
	function append ($name, $node) {
		$newnode['left_target']		= $node['right_target'];
		$newnode['right_target']	= $node['right_target']+1;
		$this->_update_first_delta($newnode['left_target'], 2);
		$newnode['element_id']		= $this->_new_tree ($name, $newnode);
		return $newnode;
	}

	function before ($name, $node) {
		$newnode['left_target']		= $node['left_target'];
		$newnode['right_target']	= $node['left_target']+1;
		$this->_update_first_delta($newnode['left_target'], 2);
		$newnode['element_id']		= $this->_new_tree ($name, $newnode);
		return $newnode;
	}

	function after ($name, $node) {
		$newnode['left_target']		= $node['right_target']+1;
		$newnode['right_target']	= $node['right_target']+2;
		$this->_update_first_delta($newnode['left_target'], 2);
		$newnode['element_id']		= $this->_new_tree ($name, $newnode);
		return $newnode;
	}



	/* ******************************************************************* */
	/* Tree Reorganization */
	/* ******************************************************************* */

	/* all nstMove... functions return the new position of the moved subtree. */
	
	/* moves the node '$src' and all its children (subtree) that it is the next sibling of '$destination'. */
	function move_after($src, $destination) {
		if ($this->valid_node($src) && $this->valid_node($destination)){
			//print "<p>move node/subtree down</p>";
			$paramnode =$this->_move_node($src, $destination['right_target']+1);
		}
		else{
			throw new Exception("can't move after: no src or dest");
		}
		return $paramnode;
	}

	/* moves the node '$src' and all its children (subtree) that it is the prev sibling of '$destination'. */
	function move_before($src, $destination) {
		if ($this->valid_node($src) && $this->valid_node($destination)){
			//print "<p>move node/subtree up</p>";
			$paramnode = $this->_move_node($src, $destination['left_target']);
		}
		else{
			throw new Exception("can't move before: no src or dest");
		}
		return $paramnode;
	}

	/* moves the node '$src' and all its children (subtree) that it is the first child of '$destination'. */
	function move_prepend($src, $destination) {
		if ($this->valid_node($src) && $this->valid_node($destination)){
			//print "<p>outdent node/subtree</p>";
			$paramnode = $this->_move_node($src, $destination['left_target']+1);
		}
		else{
			throw new Exception("can't prepend: no src or dest");
		}
		return $paramnode;
	}

	/* moves the node '$src' and all its children (subtree) that it is the last child of '$destination'. */
	function move_append($src, $destination) {
		if ($this->valid_node($src) && $this->valid_node($destination)){
			//print "<p>indent node/subtree</p>";
			$paramnode = $this->_move_node($src, $destination['right_target']);
		}
		else{
			throw new Exception("can't append: no src or dest");
		}
		return $paramnode;
	}



	/* ******************************************************************* */
	/* Tree Destructors */
	/* ******************************************************************* */

	/* deletes the entire tree structure including all records. */
	function all_delete_tree () {
		return $this->_all_delete_tree();	
	}

	/* deletes the node '$node' and all its children (subtree). */
	function delete_node ($node) {
		return $this->_delete_node($node);
	}



	/* ******************************************************************* */
	/* Tree Queries */
	/*
	 * the following functions return a valid node (L and R-value), 
	 * or L=0,R=0 if the result doesn't exist.
	 */
	/* ******************************************************************* */


	function get_node_by_id($id) { 
		return $this->get_node( "element_id=".$id);
	}
	/* returns the node that matches the left value 'leftval'. 
	 */
	function get_node_by_left($leftval) { 
		return $this->get_node( "left_node=".$leftval);
	}
	/* returns the node that matches the right value 'rightval'. 
	 */
	function get_node_by_right($rightval) {
		return $this->get_node( "right_node=".$rightval);
	}

	/* returns the first node that matches the '$whereclause' */
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

	/* ******************************************************************* */
	/* Tree Functions */
	/*
	 * the following functions return a boolean value
	 */
	/* ******************************************************************* */

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


	/* ******************************************************************* */
	/* Tree Functions */
	/*
	 * the following functions return an integer value
	 */
	/* ******************************************************************* */

	function nstNbChildren ($node) {
		return (($node['right_target']-$node['left_target']-1)/2);
	}


}

class tree_view extends tree_controller {

	/**
	 * Returns all elements as <ul>/<li> structure
	 *
	 * Possible options:
	 *  - list (simple <ul><li>)
	 *
	 * @return string
	 */
	public function toHtml($tree = null, $method = 'list')
	{
		if (empty($tree) || !is_array($tree)) {
			$nodes = $this->_get_tree();
		}
		else {
			$nodes = $tree;
		}

		if ($method == 'list') {
			$result = "<style>ul.tree ul > li {padding-left:25px}</style><ul class=tree>\n";
			$depth  = $nodes[0]['depth'];

			foreach ($nodes as $node) {

				if ($depth < $node['depth']) {
					$result .= "\n<ul>\n";
				}
				elseif ($depth == $node['depth'] && $depth > $nodes[0]['depth']) {
					$result .= "</li>\n";
				}
				elseif ($depth > $node['depth']) {
					for ($i = 0; $i < ($depth - $node['depth']); $i++) {
						$result .= "</li></ul>\n";
					}
				}

				// XXX Currently it outputs results according to my actual needs
				// for testing purpose.
				$result .= "<li>{$node['element_name']} (id: {$node['element_id']} left: {$node['left_node']} right: {$node['right_node']})";

				$depth = $node['depth'];
			}

			$result .= "</li></ul>\n";
			$result .= "</ul>\n";

			/** XXX include into test
			 *
			$ulStart = substr_count($result, '<ul>');
			$ulEnd   = substr_count($result, '</ul>');
			$liStart = substr_count($result, '<li>');
			$liEnd   = substr_count($result, '</li>');

			if ($ulStart != $ulEnd) {
				echo "Bad count of <ul> ($ulStart/$ulEnd)";
			}

			if ($liStart != $liEnd) {
				echo "Bad count of <li> ($liStart/$liEnd)";
			}
			 */

			return $result;
		}
	}
	/**
	 * Convert a tree array (with depth) into a hierarchical array.
	 *
	 * @param $tree|array   Array with depth value.
	 *
	 * @return array
	 */
	public function toArray($tree = '')
	{
		if (empty($tree) || !is_array($tree)) {
			$nodes = $this->_get_tree();
		}
		else {
			$nodes = $tree;
		}

		$result	 = array();
		$stackLevel = 0;

		if (count($nodes) > 0) {
			// Node Stack. Used to help building the hierarchy
			$stack = array();

			foreach ($nodes as $node) {
				$node['children'] = array();

				// Number of stack items
				$stackLevel = count($stack);

				// Check if we're dealing with different levels
				while ($stackLevel > 0 && $stack[$stackLevel - 1]['depth'] >= $node['depth']) {
					array_pop($stack);
					$stackLevel--;
				}

				// Stack is empty (we are inspecting the root)
				if ($stackLevel == 0) {
					// Assigning the root node
					$i = count($result);

					// $result[$i] = $item;
					$result[$i] = $node;
					$stack[] =& $result[$i];
				} else {
					// Add node to parent
					$i = count($stack[$stackLevel - 1]['children']);

					$stack[$stackLevel - 1]['children'][$i] = $node;
					$stack[] =& $stack[$stackLevel - 1]['children'][$i];
				}
			}
		}

		return $result;
	}
}

class tree extends tree_view {

}