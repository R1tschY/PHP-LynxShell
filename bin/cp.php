<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */
 
$opt = new CmdlnOptions($args);
$a = $opt->getArguments();
if (count($a) != 2) {
  lerror($args[0].': wrong usage');
}

$src = $a[1];
$dest = $a[2];

if (!is_readable($src)) {
  lerror($src.': not readable');
}

if (is_dir($dest)) {
  $dest = $dest.basename($src);
}



$success = copy($src, $dest);
if (!$success) {
  lerror('error while copying');
}
 
?>
