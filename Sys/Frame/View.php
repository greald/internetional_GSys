<?php
if(! function_exists("viewview")){ require_once SYSROOT.DIRECTORY_SEPARATOR."viewview.php"; }
if(! function_exists("fakeRoute")){ require_once SYSROOT.DIRECTORY_SEPARATOR."fakeRoute.php"; }

class Indent
{
  private static $s = 0;
  private static function tb()
  {
    $tab=""; 
    for($i=0; $i< self::$s; $i++)
    {
      $tab.="\t"; 
    } 
    return $tab; 
  }
  static function shift($n=1)
  // shift indent $n steps to the right
  {
    $s = self::$s;
    $s += $n; 
    $s = $s<0 ? 0 : $s;
    self::$s = $s;
  }
  static function T($n=1)
  // return indent, shifted $n steps to the right
  {
    self::shift($n);
    return self::tb(); 
  }
  static function nT($n=1)
  // return newline indent, shifted $n steps to the right
  {
    return "\n".self::T($n);
  }  
}

class View
{
// @param viewname: string view file's path, stripped from extension .view.php
// @param viewvars: associative array of variables to be called in viewfile
// with viewvars-key 'includes' RESERVED for cascading View instances to be incorporated
// @param ext: string, defaults to ".view.php"

  ///////////////////////////////////////////////////////////////////////////////////
  // Viewfile approach //////////////////////////////////////////////////////////////
  
  function __construct( string $viewname = "", array $viewvars = [], string $ext = ".view.php" )
  {
    $this->viewname = $viewname; // i.e path/filename WITHOUT extension "view".$ext
    $this->viewfile = $this->viewname.$ext;
    $this->viewvars = array_merge( $this->viewvars, $viewvars );
  }
  
  protected $viewname = "";
  protected $viewfile = "";
  protected $viewvars = [ 'includes'=>[] ];
  
  public function nest( View $instance )
  {
    $this->viewvars['includes'][] = $instance ; 
    return $this; 
  }
  
  static function wrapup( View $wrapper, View $content )
  {
    $wrapper->nest($content);
    return $wrapper;
  }

  public function view( )
  {
    foreach($this->viewvars as $var => $value)
    {
      // check: https://www.php.net/manual/en/language.variables.variable.php
      $$var = $value; 
    }
    unset($var);unset($value);
    // if( function_exists("peek") ){ peek(" Viewdata ".json_encode($viewdata), "\n<br/>".__FILE__." ".__LINE__);}
    
    if(is_file( $this->viewfile ))
    {
      require $this->viewfile; 
    }
    else{ echo "no such view ". $this->viewfile; }
  }

  ///////////////////////////////////////////////////////////////////////////////////
  // Viewstring approach ////////////////////////////////////////////////////////////
  
  // check ook: https://www.php.net/manual/en/wrappers.php.php#refsect1-wrappers.php-examples  
  static function fakeEval($phpCode = "") 
  {
  // source: https://gonzalo123.com/2012/03/12/how-to-use-eval-without-using-eval-in-php/
    if(strlen($phpCode)>0)
    {
      $tmpfname = APPROOT.DIRECTORY_SEPARATOR."fak";
      $handle = fopen($tmpfname.".view.php", "w+");
      fwrite($handle, "<?php\n" . $phpCode);
      fclose($handle);
      
      //include $tmpfname;
      
      //unlink($tmpfname);
      //return get_defined_vars();
      return $tmpfname;  
    }
    else
    {
      return;
    }
  }
  public function phpOut($phpCodeString){ return self::fakeEval($phpCodeString); }
}