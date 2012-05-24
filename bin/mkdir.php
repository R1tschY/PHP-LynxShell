<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */
 
$opt = new CmdlnOptions($args);
$dirs = $opt->getArguments();
if (count($dirs) < 1) {
  lerror($args[0].': missing operand');
}

$parents = $opt->isOptionSet('p', 'parents');
foreach ($dirs as $dir) {
  @mkdir($dir, PERMISSIONS, $parents) or lerror("Unable to create $dir");
}
 
?>
