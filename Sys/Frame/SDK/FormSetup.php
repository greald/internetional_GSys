<?php
//echo realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Formcontroller.php");
require_once realpath(__DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."Formcontroller.php");

class FormSetup extends Formcontroller
{
//public function buildForm(){ $this->viewstring( self::generateFormelements() );}
  public function createForm(){ $this->viewfile(__DIR__.DIRECTORY_SEPARATOR.'FormSetup.view',[]);}
}