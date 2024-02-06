<?php
require_once SYSROOT.
DIRECTORY_SEPARATOR."Frame".
DIRECTORY_SEPARATOR."Controller.php";

class Page extends Controller
{
  public function document( View $content )
  {
    return $this->viewdoc( $content );
    // ARCHIVED
    $body = new View( APPROOT."/Page/body", []); //["title"=>"Palepage","chapter"=>"Chapter Four"]);//,"content"=>$content]);
    $body = $body->nest($content);
    $head = new View( APPROOT."/Page/head", ["title"=>"Tale","font_size"=>"3vh","background_color"=>"#fedcba"] );
    
    $html = new View( APPROOT."/Page/html", ["title"=>"htmltitle"] );
    $html = $html->nest($head)->nest($body);
    
    $doc  = new View( APPROOT."/Page/doctype", [] );
    $doc  = $doc->nest($html);
    
    return $doc->view();
  }
  
  public function viewdoc( View $content )
  {
    $body = new View( APPROOT."/Page/body", [] );
    $body = $body->nest($content);
    
    $head = new View( APPROOT."/Page/head", ["title"=>"association", "font_size"=>"3vw", "background_color"=>"#abcdef"] );
    $html = new View( APPROOT."/Page/html", ["title"=>"htmltitle"] );
    $doctype = new View( APPROOT."/Page/doctype", [] );

    foreach ([["html"=>"head"], ["html"=>"body"], ["doctype"=>"html"]] as $nest )
    {
      $par= key($nest); 
      $chi = $nest[$par]; 
      $$par = $$par->nest($$chi);
    }
    return $doctype->view();
  }
  
  public function doc( $viewname = "", $viewpar = [] )
  {
    $viewname = strlen($viewname)>0 ? $viewname : APPROOT."/Page/index";
    $content = new View( $viewname, $viewpar );
    return $this->viewdoc( $content );
  }
  
  public function stringdoc( $viewname, ...$viewparameters )
  {
    $viewpar = [];
    foreach ( $viewparameters as $viewparameter )
    {
      $viewpar[] = $viewparameter;
    }
    return $this->doc( $viewname, $viewpar );
  }
}

// ( auxiliary function: content() )
function content($C){return $C;}
$C  = "\n\t\t<p>Once upon a time, in a land here far away.</p>"; 
$C .= "\n\t\t<p>a princess lived with unmatched beauty.</p>"; 
$C .= "\n\t\t<p>Face the dragon.</p>"; 
$C .= "\n\t\t<p>Dark was the night.</p>"; 
$C .= "\n\t\t<p>The stars were bright.</p>"; 
$C .= "\n\t\t<p>A prince came to ride.</p>"; 
$C .= "\n\t\t<p>They lived happily ever after.</p>";