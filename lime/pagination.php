<?php
namespace lime;

class pagination {// extends Model {
	/**
	* 유동적 페이지 리스팅
	* 현재 페이지가 항상 가운데에 오도록 정렬
	* @param    integer     $total      :   전체 글수
	* @param    integer     $pg         :   현재 페이지
	* @param    integer     $records_per_page      :   한 페이지당 글 목록수
	* @param    integer     $pages_per_block      :   표시 페이지 수
	* @return   array                   :   페이지 리스트 배열
	*/
	function ___Dynamics($total, $pg,  $records_per_page = 16, $pages_per_block= 15)
	{
		$array = array();
		$pages_per_block--;
		if ($pg    <= 0) $pg    = 1;
		if ($total <= 0) $total = 1;

		$totalPages	= ceil($total / $records_per_page);
		/* 엉뚱한 페이지 번호가 입력되었을때 마지막 페이지로 강제 변환 */
		
		if($pg > $totalPages) {
			$pg = $totalPages;
		}

		$middlePage						= round($pages_per_block / 2);
		$startRecord					= ($pg - 1) * $records_per_page;
		$startNumber					= $total - ($pg - 1) * $records_per_page;	// dynamic 의 startRecord 분석
		$startPage						= max($pg - $middlePage, 1);
		$endPage						= min($startPage + $pages_per_block, $totalPages);
		$startPage						= $endPage - $middlePage < $pg ? $startPage - $middlePage  : $startPage;
		
		$array['ThisPage']				= $pg;
		//		$array['Limit']			= $limit;
		$array['TotalPages']			= $totalPages;
		$array['StartPage']				= $startPage;
		$array['EndPage']				= $endPage;
		$array['PrevPage']				= max($pg - $pages_per_block -1, 1);
		$array['NextPage']				= ($totalPages <= $pages_per_block) ? $totalPages : min($pg + $pages_per_block +1, $totalPages);
		//		$array['IsPrevPage']	= ($array['PrevPage'] === 1) ? false : true;
		//		$array['IsNextPage']	= ($array['NextPage'] === $totalPages) ? false : true;
		$array['IsPrevPage']			= ($array['StartPage'] == 1) ? false : true;
		$array['IsNextPage']			= (($array['ThisPage'] + $middlePage ) >= $totalPages ) ? false : true;
		$array['start']					= $startNumber;

		$array['startRecord'] = $startRecord;
		for ($pageno = $array['StartPage']; $pageno <= $array['EndPage']; $pageno++) {
			$pagingNumData[] = array('PageNo'=>$pageno);
		}
		$array['pagingNumData'] = $pagingNumData;
		$array['middlePage'] = $middlePage;
		$array['total'] = $total;
	//	print_p($array);
		return $array;
	}

	public static function Dynamics($total = 1, $page = 1,  $records_per_page = 16, $pages_per_block= 15)
	{
		$paging						= array();
		$pages_per_block			= $pages_per_block %2 == 0? $pages_per_block - 2 :	$pages_per_block - 1;//홀수여야 중앙에 위치한다
		$page						= $page		<= 0 ? 1 : $page;
		$total						= $total	<= 0 ? 1 : $total;

		$totalPage					= @ceil($total / $records_per_page);
		$page						= $page > $totalPage ? $totalPage : $page;

		$middlePage					= round($pages_per_block / 2);// - 1;
		$startRecord				= ($page - 1) * $records_per_page;
		$startNumber				= $total - ($page - 1) * $records_per_page;	// dynamic 의 startRecord 분석
		$startPage					= max($page - $middlePage, 1);
		$endPage					= min($startPage + $pages_per_block, $totalPage);
		$startPage					= $endPage - $middlePage <= $page ? $startPage - $middlePage +($endPage - $page)   : $startPage ;

		$paging['thisPage']			= $page;
		$paging['totalPage']		= $totalPage;
		$paging['startPage']		= $startPage > 0 ? $startPage : 1;
		$paging['endPage']			= $endPage;
		$paging['prevPage']			= max($page - $pages_per_block -1, 1);
		$paging['nextPage']			= ($totalPage <= $pages_per_block) ? $totalPage : min($page + $pages_per_block +1, $totalPage);

		if($paging['startPage'] > 1 && $paging['startPage'] == $paging['prevPage']) {
			$paging['startPage']	+= 1;
			$paging['prevPage']		+= 1;
		}

		$paging['startNumber']		= $startNumber;
		$paging['startRecord']		= $startRecord < 0 ? 0 : $startRecord;;
		$arrNumData = array();
		for ($pageno = $paging['startPage']; $pageno <= $paging['endPage']; $pageno++) {
			$tmp = array();
			$tmp['pageNo'] = $pageno;
			if($page == $pageno) {
				$tmp['selected'] = true;
			}
			$arrNumData[] = $tmp;
		}

		$paging['arrNumData']		= $arrNumData;

		$paging['isFirst']			= ($paging['startPage'] == 1) ? false : true;
		$paging['isPrev']			= $paging['isFirst'] && $paging['prevPage'] != 1 ? true : false;
		$paging['isLast']			= (($paging['thisPage'] + $middlePage ) > $totalPage ) || $paging['nextPage'] == $paging['endPage'] ? false : true;
		$paging['isNext']			= $paging['isLast'] && $paging['nextPage'] != $paging['totalPage'] ? true : false;

		$paging['isFirstSymbol']	= 1 < $paging['startPage'] - 1 && 2 != $paging['prevPage'];
		$paging['isPrevSymbol']		= 1 < $paging['startPage']	- $paging['prevPage'];
		$paging['isNextSymbol']		= 1 < $paging['nextPage']	- $paging['endPage'];
		$paging['isLastSymbol']		= 1 < $paging['totalPage']	- $paging['endPage'] && ($paging['nextPage'] + 1) != $paging['totalPage'];

		$paging['total'] = $total;
		return $paging;
	}

	public function Statics($total, $pg, $records_per_page = 15, $pages_per_block = 16)
	{

		if ($total <= 0) $total = 1;
		$totalPages   = ceil($total / $records_per_page); //total pages_per_block
		
		/* 엉뚱한 페이지 번호가 입력되었을때 마지막 페이지로 강제 변환 */
		if($pg > $totalPages) {
			$pg = $totalPages;
		}
		if ($pg <= 0) $pg = 1;
		
		$startNumber  = $total - ($pg - 1) * $records_per_page;	// dynamic 의 startRecord 
		$currentBlock = ceil($pg / $pages_per_block);            //current block
		$totalBlocks  = ceil($totalPages / $pages_per_block);          //total blocks
		$startRecord  = ($pg-1) * $records_per_page;             //start record in current page. page1->0, page2->20,..
		$startPage    = ($currentBlock-1) * $pages_per_block;          //start page num in current block. block1->0, block2->10...
/*추가*/
		$startPage    = $startPage <= 0 ? 0 : $startPage;
		$endPage      = ($totalPages > $startPage + $pages_per_block) ? ($startPage + $pages_per_block) : $totalPages;
/*삭제
		if($pg > 1 && $startPage == 0) {
			$startPage += 1;
			$endPage += 1;
		}

		if($pg > 1 && $totalPages == $endPage ) {
			$startPage -= 1;
		}
*/		
		$array['PrevPage']   = ($currentBlock-1)*$pages_per_block;//max($pg - $pages_per_block -1, 1);
		$array['NextPage']   = $currentBlock * $pages_per_block +1;

		$array['IsPrevPage'] = $currentBlock > 1 ? true : false;
		$array['IsNextPage'] = $currentBlock < $totalBlocks ? true : false;



		while ($startPage < $endPage) {
			$a = ++$startPage;
			$pagingNumData[] = array('PageNo'=>$a); // paging
		}

		$array['ThisPage']      = $pg;
		$array['startRecord']   = $startRecord;
		$array['pagingNumData'] = $pagingNumData;

		$array['ThisPage']   = $pg;
		$array['start']      = $startNumber;
		$array['StartPage']  = $pagingNumData[0]['PageNo'];
		$array['EndPage']    = $endPage;
		$array['TotalPages'] = $totalPages;

		return $array;
	}
}


/*
<< Prev 1 2 ... 8 9 10 11 12 13 14 ... 17 18 Next >>

	<div id="myphoto-paging">
		<div class="pagination">
			{?1 < paging.ThisPage }
				<a href='/board/list_article/{=board.board_id}/{paging.ThisPage - 1}' title="{=paging.ThisPage - 1}">&lt; Prev</a>
			{:}
				<span class="disabled">&lt; Prev</span>
			{/}
			{?paging.ThisPage == 1}
			{:}
				{?paging.StartPage > 1}
					<a href='/board/list_article/{=board.board_id}/1' title="1">1</a>
				{/}
				{?paging.StartPage > 2}
					<a href='/board/list_article/{=board.board_id}/2' title="2">2</a>
				{/}
				{?paging.StartPage > 3}
					...
				{/}
			{/}
			{@paging.pagingNumData}
				{?.PageNo == paging.ThisPage}
					<span class="current">{=.PageNo}</span>
				{:}
					<a href="/board/list_article/{=board.board_id}/{=.PageNo}" title="{=paging.PageNo}">{=.PageNo}</a>
				{/}
			{/}
			{?paging.ThisPage == paging.TotalPages}
			{:}
				{?paging.EndPage + 3 <= paging.TotalPages }
					...
				{/}
				{?paging.EndPage + 2 <= paging.TotalPages}
					<a href='/board/list_article/{=board.board_id}/{=paging.TotalPages - 1}' title="{=paging.TotalPages - 1}">{=paging.TotalPages - 1}</a>
				{/}
				{?paging.EndPage + 1 <= paging.TotalPages}
					<a href='/board/list_article/{=board.board_id}/{=paging.TotalPages}' title="{=paging.TotalPages}">{=paging.TotalPages}</a>
				{/}
			{/}
			{?paging.TotalPages == paging.ThisPage}
				<span class="disabled">Next &gt;</span>
			{:}
				<a href='/board/list_article/{=board.board_id}/{=paging.ThisPage + 1}' title="{=paging.ThisPage + 1}">Next &gt;</a>
			{/}
		</div>
	</div>
*/
