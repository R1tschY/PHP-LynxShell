<?php
 Answer::addOutput('o', 'Allgemeine Infos:');
 Answer::addOutput('o', '  uname -a : ' . php_uname());
 Answer::addOutput('o', '  Benutzer : ' . get_current_user(). '('.getmyuid().')');
 Answer::addOutput('o', '  Gruppe   : ('.getmygid().')');
 Answer::addOutput('o', '  Heimverzeichnis : '.$home.' (evtl. geraten)');
 
 
 function printPHPOptions() {
  $a = ini_get_all(null, false);
  foreach ($a as $key => $value) {
    Answer::addOutput('o', str_pad('  '.$key, 30).' : '.print_var($value));
  }
 }
 
 Answer::addOutput('o', 'PHP Optionen:');
 printPHPOptions();

?>