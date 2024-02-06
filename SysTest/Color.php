<?php
require_once SYSROOT.
DIRECTORY_SEPARATOR."Frame".
DIRECTORY_SEPARATOR."Controller.php";

class Color extends Controller
{
  public function halfwayBright($CC)
  {
    return dechex((hexdec($CC) + hexdec("FF"))/2);
  }
  public function halfwayWhite($R,$G,$B)
  {
    return halfwayBright($R).halfwayBright($G).halfwayBright($B);
  }

  public function rati($D,$B){return (255-hexdec($B))/(255-hexdec($D));}
  public function transC($C, $i)
  { 
    $ratiARR = [0.29411764705882, 0.36974789915966, 0.41176470588235];//[0.86574074074074, 0.65384615384615, 0.61077844311377]; 
    return dechex( $ratiARR[$i]*hexdec($C) + (1-$ratiARR[$i])*255 );
  }

  public function trans($R,$G,$B)
  {
    return transC($R, 0).transC($G, 1).transC($B, 2);
  }
  
  public function shine()
  {
    global $GHvkleur; // from config.php
    echo basename(__FILE__,".php")." ".__LINE__."\n"; print_r($_GET);
    //$this->viewfile(APPROOT."/colors/colors.view.php",["GHvkleur"=>$GHvkleur]);
    return (new View( APPROOT."/colors/colors", [ "GHvkleur" => $GHvkleur ] )); //$CONFIG["GHvkleur"] ]));
    
    peek("kleuren", __FILE__." ".__LINE__,"testbeeld GSys-view");
  }
}



