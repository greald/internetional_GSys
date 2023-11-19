<?php
function viewview( string $viewname, array $viewvars = [], string $ext = ".php" )
{
  require_once __DIR__.
  DIRECTORY_SEPARATOR."Frame".
  DIRECTORY_SEPARATOR."Controller.php";
    
  (new Controller)->viewfile( $viewname , $viewvars );
}