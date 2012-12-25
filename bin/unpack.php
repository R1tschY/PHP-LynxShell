<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */

////////////////////////////////////////////////////////////////////////////////
// RAR
////////////////////////////////////////////////////////////////////////////////
function unrar($file, $opts) {
  if (!requireClasses("RarArchive")) return ;

  $rar = RarArchive::open($file);

  foreach($rar->getEntries() as $entry) {
    $entry->extract(''); // TODO: password?
  }
  
  $rar->close();
}

////////////////////////////////////////////////////////////////////////////////
// TAR
////////////////////////////////////////////////////////////////////////////////
function untar($file, $opts) {
  include('../libs/pclerror.lib.php3');
  include('../libs/pcltrace.lib.php3');
  include('../libs/pcltar.lib.php3');

  PclTarExtract($file); // TODO: Zugriffsrechte ändern
}

////////////////////////////////////////////////////////////////////////////////
// BZIP2
////////////////////////////////////////////////////////////////////////////////
/*function bzip2 ($in, $out, $param="1")
{
    if (!file_exists ($in) || !is_readable ($in))
        return false;
    if ((!file_exists ($out) && !is_writable (dirname ($out)) || (file_exists($out) && !is_writable($out)) ))
        return false;
    
    $in_file = fopen ($in, "rb");
    if (!$out_file = bzopen ($out, "wb".$param)) {
        return false;
    }
    
    while (!feof ($in_file)) {
        $buffer = fgets ($in_file, 4096);
        gzwrite ($out_file, $buffer, 4096);
    }

    fclose ($in_file);
    gzclose ($out_file);
    
    return true;
}*/

function bunzip2($file, $opts) {
  $dest = pathinfo($file, PATHINFO_FILENAME);
  lputs("Unpacking ".$dest);  
  if (file_exists($dest) && !is_writable($dest))
    lerror("$dest not writable");

  $ext = pathinfo($dest, PATHINFO_EXTENSION);
  if ($ext == 'tar') {
    $dest = tempnam(sys_get_temp_dir(), pathinfo($dest, PATHINFO_FILENAME)).'.tar';
  }

  $bf = bzopen($file, "r");
  $f = fopen($dest, "wb");
  
  is_resource($bf) or lerror("Unable to open $file");
  is_resource($f) or lerror("Unable to open $dest");

  while (!feof($bf)) {
    $bytes = fwrite($f, bzread($bf, BUFFER_SIZE), BUFFER_SIZE);
    ($bytes !== FALSE) or lerror('Write error');
  }

  bzclose($bf);
  fclose($f);
 
  if ($ext == 'tar') {
    untar($dest);
    unlink($dest);
  } else {
    chmod($dest, PERMISSIONS);
  } 
  
  return true;
}

////////////////////////////////////////////////////////////////////////////////
// GZIP
////////////////////////////////////////////////////////////////////////////////
/*function gzip ($in, $out, $param="1")
{
    if (!file_exists ($in) || !is_readable ($in))
        return false;
    if ((!file_exists ($out) && !is_writable (dirname ($out)) || (file_exists($out) && !is_writable($out)) ))
        return false;
    
    $in_file = fopen ($in, "rb");
    if (!$out_file = gzopen ($out, "wb".$param)) {
        return false;
    }
    
    while (!feof ($in_file)) {
        $buffer = fgets ($in_file, 4096);
        gzwrite ($out_file, $buffer, 4096);
    }

    fclose ($in_file);
    gzclose ($out_file);
    
    return true;
}*/

function gunzip($file, $opts) {
  $dest = pathinfo($file, PATHINFO_FILENAME);
  if ($opts['v']) lputs("Unpacking ".$dest);  
  if (file_exists($dest) && !is_writable($dest))
    lerror("$dest not writable");

  $ext = pathinfo($dest, PATHINFO_EXTENSION);
  if ($ext == 'tar') {
    $dest = tempnam(sys_get_temp_dir(), pathinfo($dest, PATHINFO_FILENAME)).'.tar';
  }

  $gf = gzopen($file, "rb");
  $f = fopen($dest, "wb");
  
  is_resource($gf) or lerror("Unable to open $file");
  is_resource($f) or lerror("Unable to open $dest");

  while (!gzeof($gf)) {
    $bytes = fwrite($f, gzread($gf, BUFFER_SIZE), BUFFER_SIZE);
    ($bytes !== FALSE) or lerror('Write error');
  }

  gzclose($gf);
  fclose($f);
  
  if ($ext == 'tar') {
    untar($dest);
    unlink($dest);
  } else {
    chmod($dest, PERMISSIONS);
  } 
  
  return true;
}

////////////////////////////////////////////////////////////////////////////////
// ZIP
////////////////////////////////////////////////////////////////////////////////
function zip_unpack_file($zip_entry, $file) {
  $size = zip_entry_filesize($zip_entry);
  $f = fopen($file, "wb");
  if (is_resource($f)) {
    while ($size > 0) {
      $read = min($size, BUFFER_SIZE);
      $buffer = zip_entry_read($zip_entry, $read);
      if ($buffer !== false) {
        fwrite($f, $buffer);
      } else {
        fclose($f);
        return false;
      }
      $size -= $read;
    }
    fclose($f);
    chmod($file, PERMISSIONS);
    return true;
  } else {
    return false; 
  }
}

function unzip($file, $opts){
  $zip = zip_open($file);
  if (is_resource($zip)) {
    while (($zip_entry = zip_read($zip)) !== false) {
      $name = zip_entry_name($zip_entry);
      
      if ($opts['v'])
        lputs("Unpacking $name");    
      
      if (substr($name, -1) == DIRECTORY_SEPARATOR) {
        if (!is_dir($name)) {
          @mkdir($name, PERMISSIONS, true) or lerror("Unable to create $name");
        }
      } else {
        zip_entry_open($zip, $zip_entry) or lerror("Unable to read $name");
        zip_unpack_file($zip_entry, $name) or lerror("Unable to unpack $name");
        zip_entry_close($zip_entry);
      }
    }    
    zip_close($zip);
  } else {
    lerror("Unable to open zip file");
  }
} 

////////////////////////////////////////////////////////////////////////////////

$opt = new CmdlnOptions($args);
$files = $opt->getArguments();
if (count($files) < 1) {
  lerror('usage: unpack [FILE]');
}
$file = &$files[0];

$opts = array(
  "v" => $opt->isOptionSet('v', 'verbose')
);

is_file($file) or lerror($file.' is not a regular file');
is_readable($file) or lerror($file.' is not readable');
is_writable(getcwd()) or lerror(getcwd().' is not writable');
  
$ext = pathinfo($file, PATHINFO_EXTENSION);
switch ($ext) {
case 'rar':
  unrar($file, $opts);
  break;

case 'zip':
  unzip($file, $opts);
  break;
  
case 'gz':
case 'gzip':
  gunzip($file, $opts);
  break;
  
case 'bz2':
case 'bzip2':
  bunzip2($file, $opts);
  break;
  
case 'tgz':
case 'tar':
  untar($file, $opts);
  break;
  
default:
  lerror($file.' uses noknown compression');
}


?>
