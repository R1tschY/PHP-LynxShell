<?php

Answer::addOutput('o', 'Allgemeine Infos:');
Answer::addOutput('o', '  uname -a : ' . php_uname());
Answer::addOutput('o', '  Benutzer : ' . get_current_user(). '('.getmyuid().')');
Answer::addOutput('o', '  Gruppe   : ('.getmygid().')');
Answer::addOutput('o', '  Heimverzeichnis : '.$home.' (evtl. geraten)');

?>
