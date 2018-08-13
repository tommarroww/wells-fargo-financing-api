<?php
/************************************************************************************************
* Copyright (C)2011 - 2018 littletzar - All Rights Reserved                                     *
* Unauthorized reproduction of this File in whole or part via any medium is strictly prohibited *
* Proprietary and confidential                                                                  *
* Written by Joshua Dale <littletzar@littletzar.com> - littletzar.com                           *
* For use with the Wells Fargo Financing API only                                               *
************************************************************************************************/

namespace WellsFargo;

/**
* This class manages formatting.
*
* @author Joshua Dale
* @created 20110523
* @modified 20180111
*/

class FormatC
{
  public static function currency($amount = NULL, $change = true, $thousands = false, $symbol = NULL, $default = '0.00')
  {
    $amount = self::number($amount, true);

    if($amount)
      return $symbol.number_format($amount, ($change ? 2 : 0), '.', ($thousands ? ',' : ''));

    return $default;
  }

  public static function number($number = NULL, $float = false, $negative = true, $default = NULL)
  {
    if(is_array($number))
      foreach($number as &$value)
        $value = self::number($value, array_slice(func_get_args(), 1));
    else
    {
      $number = ($negative && $number && $number[0] == '-' ? '-' : '').preg_replace("/[^\d".($float ? "\." : '')."]/",'',$number);

      if(!is_numeric($number))
        $number = $default;
      else
        $number = $float ? (float)$number : (int)$number;
    }

    return $number;
  }

  public static function username($username = NULL)
  {
    $username = preg_replace('/[^\w\.\-]/','',$username);

    if(isset($username[0]))
      return $username;
    else
      return NULL;
  }

  public static function word($word = NULL, $default = NULL)
  {
    $word = preg_replace("/[\W]/",'',$word);

    if(!empty($word))
      return $word;
    else
      return NULL;
  }
}
