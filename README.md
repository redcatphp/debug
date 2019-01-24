**No longer actively maintained. I work now with NodeJS and I recommand you to take a look at [di-ninja](https://github.com/di-ninja/di-ninja)**

Debug
======

Error Handler
-------------

 A simple error handler for direct debugging purpose via html output and targeting source code context of errors. It handle all errors including fatal errors and exceptions. It will start automatically in RedCat when your config set *dev.php* to true. `
```php
$errorHandler = new RedCat\Debug\ErrorHandler;
$errorHandler->handle();
```
Var Debug
---------

 This is a lighter and customizable alternative to native var\_dump with syntax highlighting in html mode and showing the file and line call of debug function by dint of backtrace.  ```php
use Debug\Vars;
```php
//direct output
Vars::debug($variable,$strlen=1000,$width=25,$depth=10); // text output
Vars::debug_html($variable,$strlen=1000,$width=25,$depth=10); // html output
Vars::debugs($variable1,$variabe2 /* , ... */); // html output
Vars::dbugs($variable1,$variabe2 /* , ... */); // text output

//output the result manualy
echo Vars::debug_html_return($variable,$strlen=1000,$width=25);
echo Vars::debug_return($variable,$strlen=1000,$width=25);
```
 There is some procedural function which call static function to *Vars* class. The procedural functions file will be included automatically when *ErrorHandler* launch *handle*. 
```php
dbug($var); //equivalent of Var::debug($var);
debug($var); //equivalent of Var::debug(_html$var);
dbugs($var,$var2); //equivalent of Var::dbugs($var,$var2);
debugs($var,$var2); //equivalent of Var::debugs($var,$var2);
```
