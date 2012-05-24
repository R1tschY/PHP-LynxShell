<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */

$opt = parse_cmdln($args);
if (!array_key_exists(0, $opt)) {
  $opt[0] = '.';
}

// Verzeichnis holen
$files = array();
$maxfilename = 10; // Längster Dateiname (gesetzt auf die Mindestspaltenlänge)
$dir = &$opt[0];
if (is_dir($dir)) {
  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      if ($file == '.' || $file == '..') continue;
      
      if (array_key_exists('F', $opt)) {
        $rfile = $dir.'/'.$file;
        if (is_dir($rfile))
          $file = $file.'/';
        if (is_link($rfile))
          $file = $file.'@';
        if (is_executable($rfile))
          $file = $file.'*';
      }

      $files[] = $file;
      $len = strlen($file);
      if ($len > $maxfilename) {
        $maxfilename = $len;
      }
    }
    closedir($dh);
  }
} else {
  Answer::addOutput('e', $dir.' ist kein gültiges Verzeichnis');
  return ;
}

sort($files, SORT_STRING);

print_in_table($files, $maxfilename);

?>
