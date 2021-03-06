<?php
/*
 * ErrorHandler - Error and Exception hanlder with syntax highlighting
 *
 * @package Debug
 * @version 1.5
 * @link http://github.com/redcatphp/Debug/
 * @author Jo Surikat <jo@surikat.pro>
 * @website http://redcatphp.com
 */

namespace RedCat\Debug;
class ErrorHandler{
	private static $errorType;
	private $handle;
	private $registeredErrorHandler;
	private $debugLines;
	private $debugStyle;
	public $debugWrapInlineCSS;
	public $html_errors;
	public $loadFunctions;
	public $cwd;
	public $devLevel;
	function __construct(
		$devLevel = 2,
		$html_errors=null,
		$debugLines=5,
		$debugStyle='<style>code br{line-height:0.1em;}pre.error{display:block;position:relative;z-index:99999;}pre.error span:first-child{color:#d00;}</style>',
		$debugWrapInlineCSS='margin:4px;padding:4px;border:solid 1px #ccc;border-radius:5px;overflow-x:auto;background-color:#fff;',
		$loadFunctions=true
	){
		$this->devLevel = $devLevel;
		$this->html_errors = isset($html_errors)?$html_errors:php_sapi_name()!='cli';
		$this->debugLines = $debugLines;
		$this->debugStyle = $debugStyle;
		$this->debugWrapInlineCSS = $debugWrapInlineCSS;
		$this->loadFunctions = $loadFunctions;
		$this->cwd = getcwd();
	}
	function handle($force=false){
		$this->handle = true;
		error_reporting(-1);
		ini_set('display_startup_errors',true);
		ini_set('display_errors','stdout');
		ini_set('html_errors',$this->html_errors);
		if(!$this->registeredErrorHandler||$force){
			$this->registeredErrorHandler = true;
			set_error_handler([$this,'errorHandle']);
			register_shutdown_function([$this,'fatalErrorHandle']);
			set_exception_handler([$this,'catchException']);
			if($this->loadFunctions)
				include_once __DIR__.'/functions.inc.php';
		}
	}
	private function htmlError(){
		return $this->html_errors&&(!isset($_SERVER['REQUEST_URI'])||pathinfo($_SERVER['REQUEST_URI'],PATHINFO_EXTENSION)!='json')&&(!isset($_SERVER['HTTP_ACCEPT'])||strpos($_SERVER['HTTP_ACCEPT'],'text/html')!==false);
	}
	function printTrace($html=true){
		$html = $this->htmlError();
		if(!headers_sent()&&$html){
			header("Content-Type: text/html; charset=utf-8");
		}
		$e = new \Exception();
		$msgStr = $this->getExceptionTraceCustom($e);
		if($html){
			$msg = '';
			echo $this->debugStyle;
			echo '<pre class="error" style="'.$this->debugWrapInlineCSS.'">'."\n";
			echo htmlentities($this->getExceptionTraceCustom($e,3));
			echo '</pre>';
		}
		else{
			echo "\n".$msgStr."\n";
		}
	}
	function catchException($e){
		$html = $this->htmlError();
		http_response_code(520);
		if(!headers_sent()&&$html){
			header("Content-Type: text/html; charset=utf-8");
		}
		$msgStr = 'Exception: '.$e->getMessage().' in '.$e->getFile().' at line '.$e->getLine();
		$msgStr .= $this->getExceptionTraceCustom($e);
		if($html){
			$msg = get_class($e).': '.htmlentities($e->getMessage()).' in '.$e->getFile().' at line '.$e->getLine();
			echo $this->debugStyle;
			echo '<pre class="error" style="'.$this->debugWrapInlineCSS.'">'."\n<span>".$msg."</span>\n";
			//echo ;
			if(method_exists($e,'getData')){
				echo ':';
				var_dump($e->getData());
			}
			//echo htmlentities($e->getTraceAsString());
			echo htmlentities($this->getExceptionTraceCustom($e));
			echo '</pre>';
		}
		else{
			echo "\n".$msgStr."\n";
		}
		$this->errorLog($msgStr);
		return false;
	}
	function errorLog($msg){
		$errorDir = $this->cwd.'/.tmp/';
		$errorFile = $errorDir.'php-error.log';
		if(!is_dir($errorDir)) mkdir($errorDir,0777,true);
		file_put_contents($errorFile,$msg.PHP_EOL,FILE_APPEND);
	}
	function getExceptionTraceCustom($exception,$removeEnd=false){
		$leftExclude=null;
		$lefTrim=null;
		if($this->devLevel<2){
			$leftExclude = defined('REDCAT')?realpath(constant('REDCAT').'packages'):null;
			$lefTrim = $this->cwd.'/';
		}
		return $this->getExceptionTraceAsString($exception,$leftExclude,$lefTrim,true,$removeEnd);
	}
	static protected function getCallForFrame($frame){
		return isset($frame['class'])?$frame['class'].$frame['type'].$frame['function']:$frame['function'];
	}
	static protected function getArgsForFrame($frame){
		$args = '';
		if(isset($frame['args'])){
			$args = array();
			foreach($frame['args'] as $arg){
				if(is_string($arg))
					$args[] = "'$arg'";
				elseif(is_array($arg))
					$args[] = "Array";
				elseif(is_null($arg))
					$args[] = 'NULL';
				elseif(is_bool($arg))
					$args[] = ($arg)?"true":"false";
				elseif(is_object($arg))
					$args[] = get_class($arg);
				elseif(is_resource($arg))
					$args[] = get_resource_type($arg);
				else
					$args[] = $arg;
			}
			$args = join(", ", $args);
		}
		return $args;
	}
	function getExceptionTraceAsString($exception,$leftExclude=null,$leftTrim=null,$header=true,$removeEnd=false){
		$rtn = "\n";
		$frames = [];
		$maxFilenameLength = 0;
		$lle = $leftExclude?strlen($leftExclude):null;
		$llt = $leftTrim?strlen($leftTrim):null;
		$framesTmp = $exception->getTrace();
		if($removeEnd){
			array_splice($framesTmp,0,$removeEnd);
		}
		$framesTmp = array_reverse($framesTmp);
		foreach($framesTmp as $i=>$frame){
			if(isset($frame['file'])){
				if($leftExclude&&substr($frame['file'],0,$lle)==$leftExclude)
					continue;
				if($leftTrim&&substr($frame['file'],0,$llt)==$leftTrim)
					$frame['file'] = substr($frame['file'],$llt);
				
				$frame['file_line'] = $frame['file'].':'.$frame['line'];
				
				$maxFilenameLength = max($maxFilenameLength,strlen($frame['file_line']));
			}
			else{
				if(!isset($frames[$i-1])){
					continue;
				}
				$frame['file_line'] = '';
			}
			$frames[$i] = $frame;
		}
		$step = 1;
		$frames = array_values($frames);
		for($i=0, $c = count($frames); $i<$c; $i++){
			$frame = $frames[$i];
			$wsl = $maxFilenameLength-strlen($frame['file_line'])+4;
			if($wsl<0) $wsl = 0;
			$ws = str_repeat(' ',$wsl);
			$call = self::getCallForFrame($frame).'('.self::getArgsForFrame($frame).')';
			$ii = 1;
			while(isset($frames[$i+$ii])&&empty($frames[$i+$ii]['file_line'])){
				$call .= "\t >> \t".self::getCallForFrame($frames[$i+$ii]).'('.self::getArgsForFrame($frames[$i+$ii]).')';
				$ii++;
				$i++;
			}
			$rtn .= "#$step	{$frame['file_line']} $ws	{$call}\n";
			$step ++;
		}
		
		if($header){
			$filenameLabel = 'FILE:LINE';
			$wsl = $maxFilenameLength-strlen($filenameLabel)+4;
			if($wsl<0) $wsl = 0;
			$ws = str_repeat(' ',$wsl);
			$rtn = "\nSTEP	$filenameLabel $ws	CALL\n".$rtn;
		}
		return $rtn;
	}
	function errorHandle($code, $message, $file, $line){
		if(!$this->handle||error_reporting()===0)
			return;
		$html = $this->htmlError();
		http_response_code(520);
		if(!headers_sent()&&$html){
			header("Content-Type: text/html; charset=utf-8");
		}
		$msg = self::$errorType[$code]."\t$message\nFile\t$file\nLine\t$line";
		$this->errorLog(self::$errorType[$code]."\t$message in $file at line $line");
		if(is_file($file)){
			if($html){
				echo $this->debugStyle;
				echo "<pre class=\"error\" style=\"".$this->debugWrapInlineCSS."\"><span>".$msg."</span>\nContext:\n";
				$f = explode("\n",str_replace(["\r\n","\r"],"\n",file_get_contents($file)));
				foreach($f as &$x)
					$x .= "\n";
				$c = count($f);			
				$start = $line-$this->debugLines;
				$end = $line+$this->debugLines;
				if($start<0)
					$start = 0;
				if($end>($c-1))
					$end = $c-1;
				$e = '';
				for($i=$start;$i<=$end;$i++){
					$e .= $f[$i];
				}
				$e = highlight_string('<?php '.$e,true);
				$e = str_replace('<br />',"\n",$e);
				$e = substr($e,35);
				$x = explode("\n",$e);
				$e = '<code><span style="color: #000000">';
				$count = count($x);
				for($i=0;$i<$count;$i++){
					$y = $start+$i;
					$e .= '<span style="color:#'.($y==$line?'d00':'070').';">'.$y."\t</span>";
					$e .= $x[$i]."\n";
				}
				$p = strpos($e,'&lt;?php');
				$e = substr($e,0,$p).substr($e,$p+8);
				echo $e;
				echo '</pre>';
			}
			else{
				echo strip_tags($msg);
			}
		}
		else{
			echo "$message in $file on line $line";
		}
		return true;
	}
	function fatalErrorHandle(){
		if(!$this->handle)
			return;
		$error = error_get_last();
		if($error&&$error['type']&(E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR)){
			self::errorHandle(E_ERROR,$error['message'],$error['file'],$error['line']);
		}
	}
	static function initialize(){
		self::$errorType = [
			E_ERROR           => 'error',
			E_WARNING         => 'warning',
			E_PARSE           => 'parsing error',
			E_NOTICE          => 'notice',
			E_CORE_ERROR      => 'core error',
			E_CORE_WARNING    => 'core warning',
			E_COMPILE_ERROR   => 'compile error',
			E_COMPILE_WARNING => 'compile warning',
			E_USER_ERROR      => 'user error',
			E_USER_WARNING    => 'user warning',
			E_USER_NOTICE     => 'user notice',
			E_STRICT          => 'strict standard error',
			E_RECOVERABLE_ERROR => 'recoverable error',
			E_DEPRECATED      => 'deprecated error',
			E_USER_DEPRECATED => 'user deprecated error',
		];
		if(defined('E_STRICT'))
		  self::$errorType[E_STRICT] = 'runtime notice';
	}
	static function errorType($code){
		return isset(self::$errorType[$code])?self::$errorType[$code]:null;
	}
}
ErrorHandler::initialize();