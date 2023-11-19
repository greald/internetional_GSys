<?php
if(! function_exists("viewview")){ require_once SYSROOT.DIRECTORY_SEPARATOR."viewview.php"; }
if(! function_exists("fakeRoute")){ require_once SYSROOT.DIRECTORY_SEPARATOR."fakeRoute.php"; }

require_once "Model.php";
require_once "View.php";

class Controller
{
  public function index($R="FF",$G="FF",$B="FF") //"44","88","99" // "a1","c3","cc" //"d0","e1","e5"
  {
    // your controller index method will override parent's
    $projectpath = explode( DIRECTORY_SEPARATOR, APPROOT );
    $projectname = array_pop($projectpath);
    echo "<!DOCTYPE html><html><head><style>body {background-color: #".
      $R.$G.$B.
      ";}</style></head><body><h1>".$projectname."</h1>";
    echo "<sup style=\"position: fixed; bottom:1em; right:1em\">Internetional GSys&copy;GHvernuft.nl</sup></body></html>";
  }
  
  public function viewfile( string $viewname, array $viewvars = [], string $ext = ".php" )
  {
  // @param: $viewfile: path + filename without extension
  //                    holding php-script returning html | js | xml | php | ...
  // @param: $viewdata as [ string: variable-name => mixed: value ]
  // return: 
    $View = new View( $viewname, $viewvars, $ext );
    return $View->view();
  }
  
  public function viewstring( string $str = "" )
  {
    echo $str;
  }
}