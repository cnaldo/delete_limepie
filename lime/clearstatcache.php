<?php

namespace lime;

/**
* 캐쉬 사용 함수 캐쉬 삭제
*/
function stat($fpth) {
	$arr = \stat($fpth);
	clearstatcache();
	return $arr;
}
function lstat($fpth) {
	$arr = \lstat($fpth);
	clearstatcache();
	return $arr;
}
function file_exists($fpth) {
	$r = \file_exists($fpth);
	clearstatcache();
	return $r;
}
function is_writable($fpth) {
	$r = \is_writable($fpth);
	clearstatcache();
	return $r;
}
function is_readable($fpth) {
	$r = \is_readable($fpth);
	clearstatcache();
	return $r;
}
function is_executable($fpth) {
	$r = \is_executable($fpth);
	clearstatcache();
	return $r;
}
function is_file($fpth) {
	$r = \is_file($fpth);
	clearstatcache();
	return $r;
}
function is_dir($fpth) {
	$r = \is_dir($fpth);
	clearstatcache();
	return $r;
}
function is_link($fpth) {
	$r = \is_link($fpth);
	clearstatcache();
	return $r;
}
function filectime($fpth) {
	$r = \filectime($fpth);
	clearstatcache();
	return $r;
}
function fileatime($fpth) {
	$r = \fileatime($fpth);
	clearstatcache();
	return $r;
}
function filemtime($fpth) {
	$r = \filemtime($fpth);
	clearstatcache();
	return $r;
}
function fileinode($fpth) {
	$r = \fileinode($fpth);
	clearstatcache();
	return $r;
}
function filegroup($fpth) {
	$r = \filegroup($fpth);
	clearstatcache();
	return $r;
}
function fileowner($fpth) {
	$r = \fileowner($fpth);
	clearstatcache();
	return $r;
}
function filesize($fpth) {
	$r = \filesize($fpth);
	clearstatcache();
	return $r;
}
function filetype($fpth) {
	$r = \filetype($fpth);
	clearstatcache();
	return $r;
}
function fileperms($fpth) {
	$r = \fileperms($fpth);
	clearstatcache();
	return $r;
}
