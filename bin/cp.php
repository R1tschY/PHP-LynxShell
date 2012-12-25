<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */
 
function is_url($url){
	return filter_var($url, FILTER_VALIDATE_URL) !== FALSE;
}
 
/** from http://php.net/manual/de/function.copy.php#91256
 * Copy file or folder from source to destination, it can do
 * recursive copy as well and is very smart
 * It recursively creates the dest file or directory path if there weren't exists
 * Situtaions :
 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest     
 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
 * @todo
 *     - Should have rollback technique so it can undo the copy when it wasn't successful
 *  - Auto destination technique should be possible to turn off
 *  - Supporting callback function
 *  - May prevent some issues on shared enviroments : http://us3.php.net/umask
 * @param $source //file or folder
 * @param $dest ///file or folder
 * @param $options //folderPermission,filePermission
 * @return boolean
 */
function smart_copy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
{
  $result=false;
 
  if (is_file($source)) {
    if (!is_readable($source)) {
      lerror($src.': not readable');
    }
  
    if ($dest[strlen($dest)-1] == '/' || is_dir($dest)) {
      if (!file_exists($dest)) {
        mkdir($dest, $options['folderPermission'], true) or lerror("Unable to create $dest");
      }
      $__dest = $dest.'/'.basename($source);
    } else {
      $__dest = $dest;
    }
    copy($source, $__dest) or Answer::addOutput('e', "$source: copying to '$__dest' failed");
    chmod($__dest, $options['filePermission']);
     
  } elseif (is_dir($source)) {
    if (!is_readable($source)) {
      lerror($source.': not readable');
    }  
  
    if ($dest[strlen($dest)-1] == '/') {
      if ($source[strlen($source)-1] != '/') {  
        $dest = $dest.'/'.basename($source);
      }
    } else {
      if (is_dir($dest)) {
        $dest = $dest.'/'.basename($source);
      } elseif (file_exists($dest)) {
        lerror("$dest: cannot copy directory $source to existing non-directory");
      }
    }
    
    if (!file_exists($dest)) {
      mkdir($dest, $options['folderPermission'], true) or lerror("$dest: cannot create directory");  
    }

    $dirHandle = opendir($source);
    while ($file = readdir($dirHandle)) {
      if ($file != "." && $file != "..") {
        if (!is_dir($source.'/'.$file)) {
          $__dest = $dest.'/'.$file;
        } else {
          $__dest = $dest.'/'.$file;
        }
        $result = smart_copy($source.'/'.$file, $__dest, $options);
      }
    }
    closedir($dirHandle);

  } elseif (is_url($source)) {
    if ($dest[strlen($dest)-1] == '/' || is_dir($dest)) {
      if (!file_exists($dest)) {
        mkdir($dest, $options['folderPermission'], true) or lerror("Unable to create $dest");
      }
      $__dest = $dest.'/'.basename($source);
    } else {
      $__dest = $dest;
    }
    copy($source, $__dest) or Answer::addOutput('e', "$source: copying to '$__dest' failed");
    chmod($__dest, $options['filePermission']);
    
  } else {
    lerror("$source: not copyable file type");
  }
  return $result;
}
 
$opt = new CmdlnOptions($args);
$a = $opt->getArguments();
if (count($a) != 2) {
  lerror($args[0].': wrong usage');
}

$src = $a[0];
$dest = $a[1];

smart_copy($src, $dest);
 
?>
