<?php
function isifisset( $sample, $notset = NULL, $arrName = NULL )
{
  $superglobals = ["GLOBALS","_SERVER","_GET","_POST","_FILES","_COOKIE","_SESSION","_REQUEST","_ENV"];
  
  if (isset($arrName))
  {
    // https://www.php.net/manual/en/language.variables.variable.php#128420
    // but that's a hack against php.net documentation
    if(in_array($arrName, $superglobals)){ global $$arrName; }
    
    $arr = $$arrName;
    return isset($arr[$sample]) ? $arr[$sample] : $notset ; 
  }
  else {return isset($sample) ? $sample : $notset ; }
}