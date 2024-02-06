<?php
require_once SYSROOT.
DIRECTORY_SEPARATOR."Frame".
DIRECTORY_SEPARATOR."Controller.php";

class Start extends Controller
{
/*  public function index()
  {
    return parent::$index();
    return $this->sitemap();
  }
*/  
  public function home()
  {
    return "welcome home";
  }
  
  public function sitemap()
  {
    global $routes;
    $urls = "";
    
    // https://stackoverflow.com/questions/6768793/get-the-full-url-in-php
    $pageurl = (
      isset($_SERVER['HTTP_SCHEME']) 
      ? $_SERVER['HTTP_SCHEME'] 
      : "http"
    ).
    "://".$_SERVER['HTTP_HOST']."".$_SERVER['PHP_SELF'];
    
    foreach($routes as $artifact => $route )
    {
      $url = $pageurl."?".$artifact;
      $urls .= Indent::nT(0) ."<a href=\"".$url."\">".$artifact."</a><br/>";
    }
    //$this->viewstring( "<h1>site urls</h1>".$urls );
    //
    $this->page( Indent::nT(1) . "<h1>site urls</h1>". Indent::nT() . $urls );
  }

  public function scriptinfo()
  {
    return phpinfo();
  }
  
  public function viewpage( View $viewdoc )
  {
    require_once __DIR__."/Page/Page.php";
    (new Page)->document($viewdoc);
  }

  public function page( string $bodystring = "Knowledge is organized associatively, not hierarchically.", $viewvars=[] )
  {
    require_once APPROOT."/Page/Page.php";
    
    $fak = View:: fakeEval( "?".">".$bodystring );
    (new Page)->document( (new View( $fak, $viewvars )));
  }

  public function gend( string $bodystring = "Knowledge is organized associatively, not hierarchically.", $viewvars=[] )
  {
    require_once APPROOT."/Page/Page.php";
    
    $fak = View:: fakeEval( "?".">".$bodystring );
    (new Page)->doc( (new View( $fak, $viewvars )));
  }

  public function testbeeld()
  {
    require_once __DIR__."/Color.php";
    require_once __DIR__."/Page/Page.php";
    (new Page)->document( (new Color)->shine() );    
  }
}