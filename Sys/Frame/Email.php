<?php
class Email
{
  static function send($to, $subject, $message, $additional_headers = [], $additional_params="")
  {
    return mail($to, $subject, $message, $additional_headers, $additional_params);
  }

  static function send_imap( $to, $subject, $message, $additional_headers = null, $cc = null, $bcc = null, $return_path = null )
  {
    if(function_exists("imap_mail"))
    { return imap_mail( $to, $subject, $message, $additional_headers, $cc, $bcc, $return_path ); }
    else { return "unknown function imap_mail"; }
  }
  
  static function test($to, $subject, $message, $additional_headers = [], $additional_params="", $cc = null, $bcc = null, $return_path = null)
  {
    return [
      "to"                  => $to, 
      "subject"             => $subject, 
      "message"             => $message, 
      "additional_headers"  => $additional_headers, 
      "additional_params"   => $additional_params,
      "cc"                  => $cc, 
      "bcc"                 => $bcc, 
      "return_path"         => $return_path
    ];
  }
}