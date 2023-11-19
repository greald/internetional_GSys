<?php
if ( !function_exists('mixed_array_unique'))
{
  function mixed_array_unique( $arr )
  {
    if ( is_array($arr))
    {
      foreach ( $arr as $key=>$val )
      {
        $arr[$key] = serialize($val);
      }
      $arr = array_unique( $arr );
      foreach ( $arr as $key=>$val )
      {
        if ( strlen($val)>0 )
        {
          $arr[$key] = unserialize($val);
        }
        else
        {
          unset( $arr[$key] );
        }
      }
    }
    return $arr;
  }
}

if ( !function_exists('multidim_array_unique'))
{
  function multidim_array_unique( array $arr )
  {
    foreach ( $arr as $key=>$val )
    {
      if ( is_array( $val ))
      {
        // RECURSIVE CALL
        $arr[$key] = multidim_array_unique( $val );
      }
      else
      {
        $arr[$key] = mixed_array_unique( $val );
      }
    }
    return mixed_array_unique( $arr );
  }
}