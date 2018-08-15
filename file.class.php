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
* This class manages file system interaction.
*
* @author Joshua Dale
* @created 20111025
* @modified 20180815
*/

class FileC
{
  public static function mkdir($d = NULL, $permissions = 0755, $recursive = true, $index = false)
  {
    /**
    * - If directory does not exist:
    * -- Create directory
    * -- If index file is requested: Add an index file to prevent browsing
    * - Return true indicating directory exists
    */
    
    if(!is_dir($d))
      try
      {
        if(mkdir($d, $permissions, $recursive))
          if($index && !is_file($d.'index.php'))
            return file_put_contents($d.'index.php','<?php header(\'location:/\')?>');
          else
            return true; //directory created
        else
          return false; //directory was not created
      }
      catch (\Exception $e)
      {
        var_dump(array($e, $d), __METHOD__);
      }

    return true; //directory already exists
  }

  public static function putContents($s = NULL, $f = NULL, $d = NULL, $append = false)
  {
    $flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;

    if($f && $d && self::mkdir($d))
      return file_put_contents($d.$f,$s, $flags);

    return false;
  }
}
