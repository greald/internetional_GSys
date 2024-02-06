<?php
//echo "\n<br/>".__FILE__.__LINE__." docroot " .$_SERVER['DOCUMENT_ROOT'];
// define SYSROOT relative to this app's directory
//$syspath = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Sys";
//echo "\n<br/>".__FILE__.__LINE__." syspath " .$syspath;
//$syspath = realpath($syspath);
//echo "\n<br/>".__FILE__.__LINE__." syspath " .$syspath;
//if(!defined('SYSROOT')){define("SYSROOT", $syspath) ;}
if(!defined('SYSROOT')){define("SYSROOT", realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Sys")) ;}
//echo "\n<br/>".__FILE__.__LINE__." " ; die( SYSROOT );

// testproject to be organised
// in directory next to Sys
require_once ".".DIRECTORY_SEPARATOR."config.php";
// run App
return route();
?>