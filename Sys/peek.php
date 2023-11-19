<?php
function peek( $about, $fileline = "\n<br/>".__FILE__.__LINE__, $indicator = "" )
{
// @param $about: any output to be displayed
// @param $fileline: __FILE__ / __METHOD__+ __LINE__ where peek is called
// @param $indicator: to tell wtf it's all about
  if( defined("PEEK") && PEEK )
  {
    $indicator = $indicator=="" ? ": " : $indicator.":\n";
    echo "\n<br/>".$fileline." ".$indicator." "; var_dump($about);
  }
  return;
}