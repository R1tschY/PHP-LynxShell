<?php

lputs('Allgemeine Infos:');
lputs('  uname -a : ' . php_uname());
lputs('  Benutzer : ' . get_current_user(). '('.getmyuid().')');
lputs('  Gruppe   : ('.getmygid().')');
lputs('  Heimverzeichnis : '.$home.' (evtl. geraten)');

?>
