<?php

function fixHtml($source, $tpl)
{
	/* 따옴표 안에 들어있을경우 /로 시작되는 경로를 보정한다. */
	$pattern='(?<=url\()\\\\*/(?:.*)(?=\))'.
			'|(?<=")\\\\*/(?:.*)(?=")'.
			"|(?<=')\\\\*/(?:.*)(?=')";
	
	$pattern='@('.$pattern.')@Uix';
	$split=preg_split($pattern, $source, -1, PREG_SPLIT_DELIM_CAPTURE);

	for ($i=1,$s=count($split); $i<$s; $i+=2) {
		if (substr($split[$i], 0, 1)==='\\') {
			$split[$i]=substr($split[$i],1);
			continue;
		}
		if(!preg_match("@^".preg_quote(BASE_PATH)."@", $split[$i])) { // 폴더가 잘 들어가있지 않을때 보정
			$split[$i]=preg_replace("@^/@", "<?php echo BASE_PATH;?>", $split[$i]);
		}
	}
	$source = implode('', $split);	

	// jquery template
	$source = preg_replace('/\${(\w+)}/', '${\\\\$1}', $source);
	return $source;
}
?>