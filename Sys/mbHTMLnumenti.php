<?php
require_once "mbHTMLnumentiFunctions.php";

if(isset($_POST))
{	$post		=	$_POST;
	$_POST		=	stripReeks($_POST);
	$safePOST	=&	$_POST;
}
if(isset($_GET)){	$_GET		=	stripReeks($_GET);}
if(isset($_COOKIE)){$_COOKIE	=   stripReeks($_COOKIE);}

function zuiver_querystring($QryStr)
{
//echo "\n<br/>". $QryStr;
parse_str ($QryStr, $QryArr);
//echo "\n<br/>";print_r($QryArr);
$QSuit="";
foreach($QryArr as $key=>$value)
{
	$QSuit .= rawurlencode(rawurldecode($key))."=".rawurlencode(rawurldecode($value))."&";
}
$QSuit = substr($QSuit,0,-1);
//echo "\n<br/>". $QSuit;
return $QSuit;
}

function vraagteken_zuiver_querystring($QryStr)
{
$QSuit = zuiver_querystring($QryStr);
if (strlen($QSuit)>0){$QSuit = "?".$QSuit;}
return $QSuit;
}
