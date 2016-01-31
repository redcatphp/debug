<?php
namespace RedCat\Debug;
class Chrono{
	static function getTime($start=null){
		if(!isset($start))
			$start = $_SERVER['REQUEST_TIME_FLOAT'];
		$chrono = microtime(true)-$start;
		return sprintf($chrono>=1?"%.2f":"%.0f", ($chrono>=1?$chrono:$chrono*(float)1000)).' '.($chrono>=1?'s':'ms');
	}
	static function getMemoryPeak(){
		$memory = memory_get_peak_usage();
		return rtrim(sprintf("%.2f",(float)($memory)/(float)pow(1024,$factor=floor((strlen($memory)-1)/3))),'.0').' '.('BKMGTP'[(int)$factor]).($factor?'B':'ytes');
	}
	static function output(){
		return self::getTime().' | '.self::getMemoryPeak();
	}
}