<?php
// project status
  if(!defined('DEVELOPMENT')){define("DEVELOPMENT", TRUE) ;} //  FALSE) ;} //
  if(!defined('PEEK')){define("PEEK", DEVELOPMENT && TRUE) ;} //  FALSE) ;} //
  if(!defined('FORMIDREQUIRED')){define("FORMIDREQUIRED", TRUE) ;} //  FALSE) ;} //
  

//////////////////////////////////////////////////////////////////////////////
// top level site settings
  // Config
  require_once SYSROOT.
  DIRECTORY_SEPARATOR."Frame".
  DIRECTORY_SEPARATOR."Config.php";

  // site level variable settings in array $CONFIG, defined in SYSROOT/Frame/Config.php
  ////////////////////////////////////////////////
  // Array: $GHvkleur, defining GHv colors
/*  require_once DOCROOT.
  DIRECTORY_SEPARATOR."html".
  DIRECTORY_SEPARATOR."RD".
  DIRECTORY_SEPARATOR."GHvKleur.php";
*/  
  $CONFIG = array_merge( $CONFIG, [
//    'GHvKleur'  =>  $GHvkleur,
  ] );
  
//////////////////////////////////////////////////////////////////////////////
// project settings
  // DBPREFIX prefix for database table names
  if(!defined('DBPREFIX')){define("DBPREFIX", "intnote") ;}
  // APPROOT
  if(!defined('APPROOT')){define("APPROOT", __DIR__) ;}

  // $routes
  if(!defined('DEFAULTROUTE')){define("DEFAULTROUTE", 'Sitemap') ;}
  if(!defined('DEFAULTCONTROLLER')){define("DEFAULTCONTROLLER", "Start") ;}
  if(!defined('DEFAULTMETHOD')){define("DEFAULTMETHOD", "sitemap") ;}
  $routes = array_merge( $routes, [
    DEFAULTROUTE  => ['controller' => DEFAULTCONTROLLER, 'method' => DEFAULTMETHOD],
    'Module'      => ['controller' => 'Mod' ,            'method' => 'Ule'],
    'Home'        => ['controller' => 'Start' ,          'method' => 'Home'],
    'Colors'      => ['controller' => 'Color' ,          'method' => 'shine'],
    
    'Page'        => ['controller' => 'Start' ,          'method' => 'page'],  
    'testbeeld'   => ['controller' => 'Start' ,          'method' => 'testbeeld'],  
  ]);
  $routes['pagina'] = ['controller' => '/Page/Page', 'method' => 'doc'];

