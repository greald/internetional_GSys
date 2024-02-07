<?php
require_once SYSROOT.DIRECTORY_SEPARATOR."Frame".DIRECTORY_SEPARATOR."Controller.php";

class Dashboard extends Controller
{
  public $dashboard = "";
  
  public function index()
  {
    $viewstring = "<!doctype html><html><body><h1>GSys sdk</h1><ul><li>
    <a href=\"./?sdk-project\">projectsetup</a></li><li>
    <a href=\"./?sdk-form\">formsetup</a></li><li>
    <a href=\"./?sdk-page\">pagesetup</a>
    </li></ul></body></html>";
    $this->viewstring($viewstring);
  }
  
  public function formsetup()
  {
    require_once SYSROOT.DIRECTORY_SEPARATOR."Frame".DIRECTORY_SEPARATOR."Formcontrol.php";
    $this->viewfile(__DIR__.DIRECTORY_SEPARATOR.'FormSetup.view',[]);
  }
  
  public function pagesetup()
  {
    $this->viewfile(__DIR__.DIRECTORY_SEPARATOR.'PageSetup.view',[]);
  }
  
  public function projectsetup()
  {
    $this->viewfile(__DIR__.DIRECTORY_SEPARATOR.'ProjectSetup.view',[]);
  }
}

