<?php
// hack alleen voor development
if(!defined('DEVELOPMENT'))
{
  if(!defined('SYSROOT')){define("SYSROOT", realpath(__DIR__."/../../Sys")) ;}
  //die(SYSROOT);
}
//require_once SYSROOT."/mbHTMLnumentiFunctions.php";
  require_once SYSROOT."/Lib/inputFilters.php";

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// class Input  method  quarantining ...
// TO BE COMPLETED WITH QUARANTINING non-whitelisted KEYS
// first to be  cleared with mbHTMLnumentiFunctions.php's clearkey($key)
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
class Input
{
  // LAYER OVER ALL
  // takes care of securing, validating and sanitizing incoming ($_POST $_GET and $_REQUEST) data

  const EMPTY = ['input'=>'secured'];
  const VARKEYS = ["_POST"=>[], "_GET"=>[], "_REQUEST"=>[], "_COOKIE"=>[]];
  
  // to filter out injection via input keys
  //static $whitelisted_keys = [];
  static $whitelisted_var_keys = self::VARKEYS;//["_POST"=>[], "_GET"=>[], "_REQUEST"=>[], "_COOKIE"=>[]];
  
  static function whitelist( $verb, $keyname )
  {
  // @param $verb: "get" | "post" | "request"
  // @param $keyname: string
    $varkeykeys = array_keys(self::VARKEYS);
  	$varkeys = ["post"=>$varkeykeys[0],"get"=>$varkeykeys[1],"request"=>$varkeykeys[2]];
    //echo "\n<br/>".__METHOD__.__LINE__." varkeykeys ";var_dump($varkeykeys);
    //echo "\n<br/>".__METHOD__.__LINE__." varkeys ";var_dump($varkeys);

  	// determine order of presence and order of variables listed in $_REQUEST
    // maintained for aesthetical reasons; though not needed after all :-P
  	$roArr = ["G"=>"get", "P"=>"post"];
  	$ro = str_split( ini_get_all()["request_order"]["local_value"] );
  	foreach ($ro as $Req=>$ord) {
  		$Request_Order[] = $roArr[$ord];
  	}
  	//echo "\n<br/>".__METHOD__.__LINE__." Request_Order ";var_dump($Request_Order);

  	$clearedschermnaam = clearkey($keyname); // clearkey() defined in mbHTMLnumentiFunctions.php
  	self::$whitelisted_var_keys[$varkeys[$verb]][]     = $clearedschermnaam;
  	self::$whitelisted_var_keys[$varkeys["request"]][] = $clearedschermnaam;
    
    if ( is_file(SYSROOT."/Lib/multidim_array_unique.php" ))
    {
    require_once( SYSROOT."/Lib/multidim_array_unique.php" );
    $verbarr = self::$whitelisted_var_keys;
    self::$whitelisted_var_keys = is_array( $verbarr ) ? multidim_array_unique( $verbarr ) : $verbarr;
    }
    
  }
  
  static private $rawpost;
  static private $rawget;
  static private $rawrequest;
  
  static private $safepost =[];
  static private $safeget =[];
  static private $saferequest =[];

  static private $post =[];
  static private $get =[];
  static private $request =[];
  static public function get_( $varname ){ return self::$$varname; }
  
  static public function quarantine()
  {
  // quarantines $_POST, $_GET, $_REQUEST in Input::$rawpost, Input::$rawget, Input::$rawrequest
  // neutralizes and presents them with Input::get_('post'), Input::get_('get'), Input::get_('request')
    
  // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
  // TO BE COMPLETED WITH QUARANTINING non-whitelisted KEYS
  // first to be  cleared with  cleanseKeys
  // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    
    // to do this only once
    if ($_POST    == self::EMPTY
    ||  $_GET     == self::EMPTY
    ||  $_REQUEST == self::EMPTY)
    { return; }
    
    $akaPOST    =& $_POST;
    $akaGET     =& $_GET;
    $akaREQUEST =& $_REQUEST;
    
    $riedel = ['POST'=>$_POST, 'GET'=>$_GET, 'REQUEST'=>$_REQUEST];
    
    foreach( $riedel as $ried => $edel)
    {
      $akaried = "aka".$ried;
      $lowried = strtolower($ried);
      $rawried = "raw".$lowried;
      $aferied = "safe".$lowried;
      if( $$akaried == self::EMPTY ){ continue; }else{
        self::$$rawried = $$akaried;
      //$edel = self::EMPTY; // doesn't work this way
        self::$$aferied = self::mBsecure( self::$$rawried );
        self::$$lowried =& self::$$aferied;
      }      
    }
    $_POST    = self::EMPTY;
    $_GET     = self::EMPTY;
    $_REQUEST = self::EMPTY;
  }
  
// Both methods sanitize() and validate() perform exactly the same
// but validate() returns bool
// and sanitize() returns unrestricted
// so validate() is just a redirect to sanitize(), but callback output restricted to bool
 
  static function sanitize($classname, $callback, $varname, $varkey) // : unrestricted
  {
//  echo "\n<br/>".__METHOD__.__LINE__." callback and pararr ";var_dump([$classname, $callback, $varname, $varkey]);
    return class_exists($classname) 
      ? call_user_func_array([$classname, $callback],[self::get_($varname)[$varkey]])
      : call_user_func_array(             $callback ,[self::get_($varname)[$varkey]]);
  }
  
  static function validate($classname, $callback, $varname, $varkey) : bool
  {
    return self::sanitize($classname, $callback, $varname, $varkey);
  }
  
  static function add2postORI($classname, array $callbacks, $postkey)
  {
    return __METHOD__." archived, harmful.";
  // @param callbacks method names for validate and sanitize respectively
  // one $postkey at a time:
  // set    self::$post[$postkey]
  // remove self::$safepost[$postkey]
    self::quarantine_post();
    if(isset(self::$safepost[$postkey]) 
    && self::validate($classname, $callbacks[0], $postkey))
    {
      self::$post[$postkey] = self::sanitize($classname, $callbacks[1], $postkey);
      unset( self::$safepost[$postkey] );
    }else{}
    //
    return self::$post;
  }
  
  static function add2post(
    array $valr = ['validator_class','validator_meth'], 
    array $sanr = ['sanitizer_class','sanitizer_meth'], 
    $postkey )
  {
    return __METHOD__." archived, harmful.";
  // @param callbacks method names for validate and sanitize respectively
  // one $postkey at a time:
  // set    self::$post[$postkey]
  // remove self::$safepost[$postkey]
    self::quarantine_post();
    if(isset(self::$safepost[$postkey]) 
    && self::validate($valr[0], $valr[1], $postkey))
    {
      self::$post[$postkey] = self::sanitize($sanr[0], $sanr[1], $postkey);
      unset( self::$safepost[$postkey] );
    }else{}
    //
    return self::$post;
  }
  
  public $checklist = [];
  public function list_checkpoint($postkey, $valclass, $valmeth, $sanclass, $sanmeth)
  {
    //self::$whitelisted_keys[] = $postkey;
    $this->checklist[$postkey] = [[$valclass, $valmeth], [$sanclass, $sanmeth]];
  }
  
  public function fullpost()
  {
    self::get_('safepost');
    foreach( self::$safepost as $key=>$value )
    {
      if( isset( $this->checklist[$key]) )
      {
        self::add2post( $this->checklist[$key][0], $this->checklist[$key][1], $key );
      }
    }
  }
  
  public static function cleanseKeys($keys) // : array
  {
	  return is_array($keys) ? clearkeys($keys) : [clearkey($keys)] ;
  }
    
  static function mBsecure( $var )
  {  
  // @param var: array | string
    require_once SYSROOT."/Lib/inputFilters.php";    
    return Sanitizer::mbVar( $var );
  }
}