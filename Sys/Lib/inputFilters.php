<?php
// hack alleen voor development
if(!defined('DEVELOPMENT'))
{
  if(!defined('SYSROOT')){define("SYSROOT", realpath(__DIR__."/../../Sys")) ;}
  //die(SYSROOT);
}
//require_once SYSROOT."/mbHTMLnumentiFunctions.php";

class specify
{
// universal specifications for data-types
  static function telephone($tolerance = "")
  {
    return $tolerance == "strict"
    ? "#(\+|0{1,2})[1-9][0-9]{8,}#"
    : "#(\+|0{1,2})\W{0,1}([1-9]\W{0,1})([0-9]\W{0,1}){8,}#";
  }
  
  static function email($EMail ="")
  {
    return filter_var($EMail, FILTER_VALIDATE_EMAIL);
  }
  
  static function NLpc( $sample )
  {
    $allow = preg_replace('/[^a-zA-Z0-9]/', "", $sample);
    
    return [ 'pattern'=>'/[1-9][0-9]{3}[A-Za-z]{2}/', 'allowed'=>$allow ];
  }
  
}

class validizer
{
// check for required type of information
  static function is_telephone($Tel = "")
  {
  	if(!(is_string($Tel) && strlen($Tel)>0)){return FALSE;}
  	$Tel = str_replace(["&#43;","\\u{2B}"], "+",  $Tel); // weer gewoon een plusje vooraan
//  	preg_match_all('/(\+|0{1,2})[1-9][0-9]+/', $Tel, $match);	
  	preg_match_all(specify::telephone("broad"), $Tel, $match);	
  	$matches = $match[0]; //print_r($matches);	
  	$valid = count($matches) >0;
  	foreach($matches as $K=>$V) 
  	{
  		$valid = ($V === $Tel) && $valid;
  	}
  	return $valid;
  }
  
  static function is_email($EMail ="")
  {
  	if(!(is_string($EMail) && strlen($EMail)>0)){return FALSE;}
  	return specify::email($EMail) == $EMail;
  }
  
  static function is_whole_decimal($getal = "")
  {
  	if(is_integer($getal)){return TRUE;}
  	if(!( is_string($getal) && strlen($getal)>0 )){return FALSE;}
  	
  	$getalArr = str_split($getal);
  	$natuurlijk = ( $getalArr != [] );
  	foreach($getalArr as $nmr => $ltt)
  	{
  		$natuurlijk = $natuurlijk && preg_match("/[0-9]/",$ltt);
  	}
  	return $natuurlijk;
  }

  static function is_datum($D = "")// dd-mm-yyyy
  {
  	if(!(is_string($D) && strlen($D)==10)){return FALSE;}
  	$DArr = explode("-",$D);
    return checkdate( $DArr[1], $DArr[0], $DArr[2] );
  }

  function is_NLpostcode($PC = "")
  {
  	if(!(is_string($PC) && strlen($PC)>=6)){return FALSE;}
  	
    $preparate = specify::NLpc( $PC );
  	preg_match($preparate['pattern'], $preparate['allowed'], $matches);
  	// var_dump($matches);
    return $matches[0] == $preparate['allowed'];
  }

}

class sanitizer
{
// transform validated data into uniform standard
  static function telephone( $tel, $net = "31" )
  {
  	//$tel = str_replace(["&#43;","\\u{2B}"], "+",  $tel); // weer gewoon een plusje vooraan
    //$tel = ltrim($tel, "+");
    $tel = preg_replace("#\D#", "", $tel); // korte metten
//    $tel = str_replace("000", $net, substr($tel, 0,3)) . substr($tel, 3);
    $tel = str_replace("00", $net, substr($tel, 0,2)) . substr($tel, 2);
    $tel = str_replace("0", $net, substr($tel, 0,1)) . substr($tel, 1);
//    echo $tel;
    return validizer::is_telefoon("+".$tel)
    ? "+".$tel
    : NULL;
  }
  
  static function email($email = "") 
  {
  // removing any erratic white spaces, and resolving case confusion
  // although: https://stackoverflow.com/a/9808332/3932895
    $email = preg_replace("#\s#","",strtolower($email));
    
    return validizer::is_email($email)
    ? $email
    : NULL;
  }
  
  static function mbVar( $var )
  {  
  // @param var: array | string
    require_once SYSROOT."/mbHTMLnumentiFunctions.php";
    
    return (is_array($var)) 
    ? stripReeks($var)
    : ( is_string($var)
      ? mbHTMLnumenti($var) 
      : ( is_numeric($var) 
        ? $var
        : NULL )
      );
  }
 
  function NLpostcode($PC = "")
  {
  	if(!(validizer::is_NLpostcode($PC))){return NULL;}
  	
    $preparate = specify::NLpc( $PC );
  	preg_match($preparate['pattern'], $preparate['allowed'], $matches);
  	// var_dump($matches);
    return $matches[0];
  }
  
}