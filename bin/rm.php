<?php

function remove_file($path) {
  if (is_writeable($path)) {
    @unlink($path) or lfputs('e', $path.': Löschen nicht möglich');
  } else {
    lfputs('e', $path.': Keine Berechtigung');
  }
}

function remove_dir($path) {
  if (is_writeable($path)) {
    @rmdir($path) or lfputs('e', $path.': Löschen nicht möglich');
  } else {
    lfputs('e', $path.': Keine Berechtigung');
  }
}

function remove_dir_recursive($path) {
  if (!is_writeable($path)) {
    lfputs('e', $path.': Keine Berechtigung');
    return ;
  } 
    
  $handle = opendir($path); 
  while($file = readdir($handle)) { 
    if ($file != '..' && $file != '.' && $file != '') {
       $fpath = $path.'/'.$file;
       if (is_dir($fpath)) {
         remove_dir_recursive($fpath);
       } else {
         remove_file($fpath); 
       }
    } 
  } 
  closedir($handle); 
  remove_dir($path); 
  
  return !is_dir($path);
}

////////////////////////////////////////////////////////////////////////////////

$opt = new CmdlnOptions($args);

$files = $opt->getArguments();
if (count($files) < 1) {
  lerror($args[0].': fehlender Operand');
}

if ($opt->isOptionSet('r', 'recursive')) {
  foreach ($files as $file) {
    if (!file_exists($file)) {
      lfputs('e', $file.': Datei oder Verzeichnis nicht gefunden');
    } else if (is_dir($file)) {
      remove_dir_recursive($file);
    } else {
      remove_file($file);
    }
  }
} else {
  foreach ($files as $file) {
    if (!file_exists($file)) {
      lfputs('e', $file.': Datei oder Verzeichnis nicht gefunden');
    } else if (is_dir($file)) {
      lfputs('e', $file.': Ist ein Verzeichnis');
    } else {
      remove_file($file);
    }
  }
}



?>
