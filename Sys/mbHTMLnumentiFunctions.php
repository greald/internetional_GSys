<?php
//============================
/* @aanroep: mbHTMLnumenti($str)
* 
* @param string $str in UTF-8
* 
* @return string $str 
* waarin alle andere tekens dan basisletters, cijfers, whitespaces en @ , . ; : 
* vervangen zijn door hun multibyte HTML numerieke entiteiten in de vorm &#xxxxx;
*/
$malicious = " vigilant ";

if(!defined("CODERING"))
{
	define("CODERING","UTF-8");
}

if(! defined("HTML_ENTITY_PATTERN"))
{
  define("HTML_ENTITY_PATTERN",'/(&#[\d]+;)|(&#x[[:xdigit:]]+;)|(&[[:alnum:]]+;)/');
}
function HTMLentity_split( $str )
{
  return preg_split( HTML_ENTITY_PATTERN, $str, -1, PREG_SPLIT_DELIM_CAPTURE );
}

function mbIni($codeset = CODERING)
{
  // https://www.php.net/manual/en/function.mb-split.php#108189
  mb_regex_encoding($codeset);
  mb_internal_encoding($codeset);
  return $codeset;
}

function mbStringToArray ($string) 
{
  $codeset = mbIni(); //met locale constante

  // source: http://php.net/manual/en/function.mb-split.php#80046
  $strlen = mb_strlen($string);
  $array=[];
  while ($strlen) 
	{
	    $array[] = mb_substr($string,0,1,$codeset);
	    $string = mb_substr($string,1,$strlen,$codeset);
	    $strlen = mb_strlen($string);
	}
  return $array;
}

function mbStringToArraySkipHTMLentities ($string) 
{
  $array = HTMLentity_split( $string );
  $charchains = [];
  foreach( $array as $index=>$charchain )
  {
    $charchains[]= [ $charchain , preg_match( HTML_ENTITY_PATTERN, $charchain )==1 ] ;
    unset( $array[ $index ] );
  }
  //return $charchains;
  $chars = [];
  foreach($charchains as $charchain )
  {
    if($charchain[1]){ $chars[]= [$charchain[0],$charchain[1]]; } // $charchain[0];} // 
    else
    {
      $array = mbStringToArray($charchain[0]);
      foreach($array as $char)
      {
        $chars[]= [ $char, $charchain[1] ]; // $char; //
      }
      unset( $array );
    }
  }
  return $chars;
}

function mbHTMLnumentiOBS($str) // geldig voor $codeset en met regular expression [^0-9a-zA-Z @.,:-] // [^0-9a-zA-Z\s@.,:-] // [^\w\s@.,;:]
{
  $codeset = mbIni(); //met locale constante
  //echo "codeset = ".$codeset;

	$str =  mbStringToArray ($str);
	
  //$str = preg_replace("/\s/", " ", $str); // noodsprong tegen new lines in strings. Daar faalt javascript mee.

	//$pattern =  "[^\w\s@.,:-]"; // faalt soms
	//$pattern =  "[^0-9a-zA-Z\s@.,:-]";
	$pattern =  "[^0-9a-zA-Z @.,:-]"; // noodsprong verholpen: alle white spaces behalve spatie worden genmbhtmlnummerd
	$strLength = sizeof($str); 
	for($L=0;$L < $strLength; $L++)
	{
		//echo "\n<br/>".$str[$L];
		if(mb_ereg_match ($pattern , $str[$L]))
		{
			//$str[$L] = mb_encode_numericentity ($str[$L], [0x0, 0xffff, 0, 0xffff], $codeset); // obs grh 20210727
			$str[$L] = "&#".mb_ord($str[$L], $codeset).";"; // nieuw grh 20210727
		}
		else
		{		
		}
		//echo " -> ".$str[$L];
	}
	return implode("",$str);
}

function mbHTMLnumenti($str) // geldig voor $codeset en met regular expression [^0-9a-zA-Z @.,:-] // [^\w\s@.,;:]
{
  global $malicious;
  
  $codeset = mbIni(); //met locale constante
	// overwriting $str by its 2-d array
  $str =  mbStringToArraySkipHTMLentities ($str);
	
	$pattern =  "[^0-9a-zA-Z @.,:-]"; // noodsprong verholpen: alle white spaces behalve spatie worden gembhtmlnummerd
	$strLength = sizeof($str); 
	for($L=0;$L < $strLength; $L++)
	{
		if(!$str[$L][1] && mb_ereg_match ($pattern , $str[$L][0]))
		{
			$str[$L] = "&#".mb_ord($str[$L][0], $codeset).";"; // nieuw grh 20210727
      $malicious = " intercepted ";
//      echo "\n<br/>".__FILE__.__LINE__.$malicious;
		}
		else
		{
      $str[$L]= $str[$L][0];	
		}
	}
	return implode("",$str); //$str; // 
}

function clearkey($key)
{
  global $malicious;
  $out = rawurlencode( mbHTMLnumenti( rawurldecode($key) ));
  $malicious = $out == $key ? $malicious : " intercepted ";
//  echo "\n<br/>".__FILE__.__LINE__.$malicious;
  return $out;
}

function clearkeys($array)
{
  foreach( $array as $key => $val )
  {
    unset($array[$key]);
    $key = clearkey($key);
    $array[$key]=$val;
  }
  return $array;
}

function stripReeksOBS($arr)
{
  $arr = is_array($arr) ? clearkeys($arr) : $arr;
  foreach($arr as $key=>$str)
  {
    if(is_array($str))
    {
      $str=stripReeks($str); // RECURSIEVE AANROEP              	
    }
    elseif(is_string($str))
    {
      $arr[$key] = mbHTMLnumenti($str);
    }
  }
  return $arr;
}

function stripReeks($arr)
{
  if(is_array($arr))
  {
    $arr = clearkeys($arr);
    foreach($arr as $key=>$str)
    {
      if(is_array($str))
      {
        $arr[$key] = stripReeks($str); // RECURSIEVE AANROEP   
      }
      elseif(is_string($str))
      {
        $arr[$key] = mbHTMLnumenti($str);
      }
    }
    return $arr;
  }
  elseif(is_string($arr))
  {
    return mbHTMLnumenti($arr);
  }
}

// decode mbHTMLnumericentity: https://www.php.net/manual/en/function.mb-chr.php#refsect1-function.mb-chr-description
function reverseMbHTMLnumenti($str): array
{
// recovers mb-numeric-HTML-entities from string to CODERING format
// returns array quarantining probably malicious strings
  $arr = HTMLentity_split( $str );
  foreach( $arr as $index => $segment )
  {
    if( substr($segment,0,2) == "&#" && substr($segment,-1) == ";" )
    {
//    echo "\n<br/>".__FILE__.__LINE__." ".$index." ". strlen($segment);
      $segment = substr($segment,2,-1);
//    echo " - ". strlen($segment)." ".$segment;
      $arr[$index] = mb_chr($segment, CODERING);
//    echo " ".$arr[$index];
    }
  }
  return $arr;
}