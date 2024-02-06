<?php
// USE ONLY in GLOBAL SCOPE
//
// isifisset0() returns:

// $arrName[$sample] or
// $sample or
// $notset
function isifisset0( $sample, $notset = NULL, $arrName = NULL )
{
  //$superglobals = ["GLOBALS","_SERVER","_GET","_POST","_FILES","_COOKIE","_SESSION","_REQUEST","_ENV"];
  
  $superglobals = ["GLOBALS"=>$GLOBALS,"_SERVER"=>$_SERVER,"_GET"=>$_GET,"_POST"=>$_POST,"_FILES"=>$_FILES,"_COOKIE"=>$_COOKIE,"_REQUEST"=>$_REQUEST,"_ENV"=>$_ENV];
  if( session_status() == PHP_SESSION_ACTIVE ){ $superglobals["_SESSION"] = $_SESSION ;}
  // echo "\n<br/>".__FILE__.__LINE__." superkeys "; var_dump(array_keys($superglobals));

  if (isset($arrName))
  {
    // https://www.php.net/manual/en/language.variables.variable.php#128420
    // but that's a hack against php.net documentation
    
    if(in_array($arrName, array_keys($superglobals)))
    {
      $arr = $superglobals[$arrName]; 
    }
    else
    {
      global $$arrName;
      echo "\n<br/>".__METHOD__.__LINE__." post ". $$arrName ." formid "; var_dump(get_defined_vars());
      $arr = $$arrName;
    }  
    
    return isset($arr[$sample]) ? $arr[$sample] : $notset ; 
  }
  else {return isset($sample) ? $sample : $notset ; }
}

// isifisset() returns:

// $arrName[$sample] or
// $sample or
// $notset
function isifisset( $sample, $notset = NULL, $arr = NULL )
{
  $result = $notset;
  if(isset($arr) && is_array($arr))
  {
    if( isset( $sample ))
    {
      if( isset( $arr[$sample] ) )
      { $result = $arr[$sample]; }
    }
  }
  elseif( isset( $sample ))
  {
    $result = $sample;
  }
  return $result;
}