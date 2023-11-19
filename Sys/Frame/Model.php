<?php
require_once "SQL.php";

// DBPREFIX prefix for database table names should have been set in project's config.php
if(!defined('DBPREFIX')){define("DBPREFIX", "gsys") ;}

class Model extends SQL
{
  
}
