<?php
function fakeRoute($controllerfile, $controller, $method, $arguments = [])
{
// @param $controllerfile string: (full) path and filename containing class $controller
// @param $controller string: $controller class name | object: $controller
// @param $method string: method name in class $controller
// @param $arguments array: arguments, ordered according to the $method's parameter list
// return: whatever $controller->$method returns
  if(is_object($controller) ){
    $cntrinstance = $controller;
//    if( function_exists("peek")){ peek( "bestaande controller opgepikt" ); }
  }
  else{
    require_once $controllerfile;
    $cntrinstance = new $controller;    
//    if( function_exists("peek")){ peek( "nieuwe controller geinstantieerd" ); }
  }
  // https://www.php.net/manual/en/functions.variable-functions.php
  // https://www.php.net/manual/en/functions.arguments.php#functions.variable-arg-list
  return $cntrinstance->$method(...$arguments); 
}