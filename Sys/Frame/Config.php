<?php
// global globals
  if(!defined('GSYS_DIR_SEP')){define("GSYS_DIR_SEP", "/") ;} 
  if(!defined('DOCROOT')){define("DOCROOT", $_SERVER['DOCUMENT_ROOT']) ;} 
  // if(!defined('DOCROOT')){define("DOCROOT", realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR.".."));} //
  if(!defined('SYSROOT')){define("SYSROOT", DOCROOT.DIRECTORY_SEPARATOR."Sys") ;}
  if(!defined('DATABASE')){define("DATABASE", SYSROOT.DIRECTORY_SEPARATOR."dbconnect.php") ;}

// $CONFIG to be extended within app's config.php
  // $CONFIG elements to construct as [ attribute => global variable]
  $CONFIG = [
    'webmaster'  =>  "webmaster@ghvernuft.nl"
  ];

// $routes to be extended within app's config.php
  // $routes elements to construct as
  // [ artifact =>[ 'controller' => path_from_APPATH_to_controller_file_without_php_extension, 'method' => ... ]]
  // order from long artifacts to short ones!
  $routes = [];
  if(DEVELOPMENT)
  {
    $routes['sdk-project'] = ['controller' => '../Sys/SDK/dashboard', 'method' => 'projectsetup'];
    $routes['sdk-form']    = ['controller' => '../Sys/SDK/dashboard', 'method' => 'formsetup'];
    $routes['sdk-page']    = ['controller' => '../Sys/SDK/dashboard', 'method' => 'pagesetup'];
    $routes['sdk']         = ['controller' => '../Sys/SDK/dashboard', 'method' => 'index'];
  }

  function route()
  {
    global $routes; 
    $defaultController = DEFAULTCONTROLLER;
    $defaultMethod = DEFAULTMETHOD;//index";

    // normally by default, as in: APPROOT-url/?controller/method/parm1/parm2/...
    $controlroute = explode(GSYS_DIR_SEP, $_SERVER['QUERY_STRING']);
//if(function_exists("peek")){peek($controlroute, NULL, " controlroute: ");}
    $controller   = array_shift($controlroute);
    $cntrmethod   = array_shift($controlroute);
    $cntrparams   = $controlroute; // i.e the remaining array. To be 'unpacked' by the ...operator as in 
    // https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list
    
    $routeKeys =  array_keys( $routes );
    rsort($routeKeys); // sorted reversely to make sure longest routekeys are evaluated first
//if(function_exists("peek")){peek($routeKeys, NULL, " routeKeys: ");}
    
    foreach ($routeKeys as $routeKey)
    {
//if(function_exists("peek")){peek(substr( $_SERVER['QUERY_STRING'], 0, strlen($routeKey)), NULL, " zoekterm: ");}
      if( $routeKey == substr( $_SERVER['QUERY_STRING'], 0, strlen($routeKey) ))
      {
        $controller = $routes[ $routeKey ]['controller'];
        $cntrmethod = $routes[ $routeKey ]['method'];
//if(function_exists("peek")){peek([$controller=>$cntrmethod], NULL, " controller=>cntrmethod: ");}
        
        // and the query string's remainder is assumed to supply the method's arguments
        $paramstr = substr_replace( $_SERVER['QUERY_STRING'], "", 0, strlen($routeKey) );
        $paramstr = trim($paramstr, GSYS_DIR_SEP);
        
        $cntrparams = explode( GSYS_DIR_SEP, $paramstr); // i.e string to array. 
        // Array is to be unpacked by the ...operator as in 
        // https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list 
        
        break;
      }
    }
    $controller = $controller == "" ? $defaultController : $controller ;
    $cntrmethod = $cntrmethod == "" ? $defaultMethod : $cntrmethod ;
     
    try
    {
//if(function_exists("peek")){peek(realpath(APPROOT."/".$controller.".php"), NULL, " controller path: ");}
      $controllerfilepath = realpath(APPROOT."/".$controller.".php");
//if (function_exists("peek")){peek($controllerfilepath, NULL, " controller path: ");}
      require_once $controllerfilepath;
      $expl = explode(GSYS_DIR_SEP, $controller);
      $controller = array_pop($expl);
//if(function_exists("peek")){peek(class_exists($controller), NULL, $controller." exists: ");}
      if(! method_exists($controller, $cntrmethod))
      {
        $alarm = " No method ".$controller." or no class ".$cntrmethod." found. ";
        throw new Exception($alarm); 
      }
    } 
    catch(Exception $e)
    {
      //echo $e->getMessage();
      $controller = $defaultController; 
      $cntrmethod = $defaultMethod;
    }
    finally
    {    
      // https://www.php.net/manual/en/functions.variable-functions.php
      // https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list
//if (function_exists("peek")){peek($cntrparams, NULL, " cntrparams: ");}
      return (new $controller)->$cntrmethod(...$cntrparams);
    }
  }

// convenience functions
  if(! function_exists("isifisset")){ require_once SYSROOT.DIRECTORY_SEPARATOR."isifisset.php"; }
  if(! function_exists("peek"))   { require_once SYSROOT.DIRECTORY_SEPARATOR."peek.php"; }