<?php
if(!function_exists('dbug')){
	function dbug(){
		return call_user_func_array(['RedCat\Debug\Vars','debug'],func_get_args());
	}
}
if(!function_exists('debug')){
	function debug(){
		return call_user_func_array(['RedCat\Debug\Vars','debug_html'],func_get_args());
	}
}
if(!function_exists('dbugs')){
	function dbugs(){
		return call_user_func_array(['RedCat\Debug\Vars','dbugs'],func_get_args());
	}
}
if(!function_exists('debugs')){
	function debugs(){
		return call_user_func_array(['RedCat\Debug\Vars','debugs'],func_get_args());
	}
}
if(!function_exists('d')){
	function d(){
		return call_user_func_array(['RedCat\Debug\Vars',php_sapi_name()=='cli'?'debugsCLI':'debugs'],func_get_args());
	}
}
if(!function_exists('dd')){
	function dd(){
		call_user_func_array('d',func_get_args());
		die;
	}
}
if(!function_exists('dj')){
	function dj(){
		$args = func_get_args();
		foreach($args as &$arg){
			$arg = json_encode($arg,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
		}
		return call_user_func_array(['RedCat\Debug\Vars',php_sapi_name()=='cli'?'debugsCLI':'debugs'],$args);
	}
}
if(!function_exists('ddj')){
	function ddj(){
		call_user_func_array('dj',func_get_args());
		die;
	}
}