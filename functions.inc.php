<?php
use RedCat\Debug\Vars;
use RedCat\Debug\ErrorHandler;
if(!function_exists('dbug')){
	function dbug(){
		return call_user_func_array([Vars::class,'debug'],func_get_args());
	}
}
if(!function_exists('debug')){
	function debug(){
		return call_user_func_array([Vars::class,'debug_html'],func_get_args());
	}
}
if(!function_exists('dbugs')){
	function dbugs(){
		return call_user_func_array([Vars::class,'dbugs'],func_get_args());
	}
}
if(!function_exists('debugs')){
	function debugs(){
		return call_user_func_array([Vars::class,'debugs'],func_get_args());
	}
}
if(!function_exists('d')){
	function d(){
		return call_user_func_array([Vars::class,php_sapi_name()=='cli'?'debugsCLI':'debugs'],func_get_args());
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
		return call_user_func_array([Vars::class,php_sapi_name()=='cli'?'debugsCLI':'debugs'],$args);
	}
}
if(!function_exists('ddj')){
	function ddj(){
		call_user_func_array('dj',func_get_args());
		die;
	}
}
if(!function_exists('dk')){
	function dk(){
		$args = func_get_args();
		foreach($args as &$arg){
			if(is_array($arg)){
				$arg = array_keys($arg);
			}
			else if(is_object($arg)){
				$tmp = [];
				foreach($arg as $k=>$v){
					$tmp[] = $k;
				}
				$arg = $tmp;
			}
		}
		return call_user_func_array([RedCat\Debug\Vars::class,php_sapi_name()=='cli'?'debugsCLI':'debugs'],$args);
	}
}
if(!function_exists('ddk')){
	function ddk(){
		call_user_func_array('dk',func_get_args());
		die;
	}
}
if(!function_exists('dtrace')){
	function dtrace(){
		static $o;
		call_user_func_array('d',func_get_args());
		if(!isset($o)){
			if(isset($GLOBALS['redcat'])){
				$o = $GLOBALS['redcat'][ErrorHandler::class];
			}
			else{
				$o = new ErrorHandler();
			}
		}
		$o->printTrace();
		return $o;
	}
}
if(!function_exists('ddtrace')){
	function ddtrace(){
		call_user_func_array('dtrace',func_get_args());
		die;
	}
}