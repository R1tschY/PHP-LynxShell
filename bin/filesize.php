<?php

/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */
 
function filesize_output($file, $print_filename) {
  $prefix = $print_filename ? ($file.': ') : '';

  if (file_exists($file)) {
    $size = filesize($file);
    
    if ($size !== FALSE) {
      lputs($prefix.$size);
    } else {
      Answer::addOutput('e', $prefix."failed");
    }
  } else {
    Answer::addOutput('e', $prefix."does not exist");
  }
}

////////////////////////////////////////////////////////////////////////////////

$opt = new CmdlnOptions($args);
$files = $opt->getArguments();
if (count($files) < 1) {
  lerror($args[0].': missing operand');
}

if (count($files) == 1) {
  filesize_output($files[0], false);
} else {
  foreach ($files as $file) {
    filesize_output($file, true);
  }
}

?>
