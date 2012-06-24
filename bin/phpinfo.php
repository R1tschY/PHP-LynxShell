<?php

Answer::addOutput('o', 'PHP version: '.phpversion().PHP_EOL.PHP_EOL);

$a = ini_get_all(null, false);
foreach ($a as $key => $value) {
  Answer::addOutput('o', str_pad($key, 30).' : '.print_var($value));
}

?>
