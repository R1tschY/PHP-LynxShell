<?php
 ob_start ();
 phpinfo();
 Answer::addOutput('o', ob_get_clean());
?>