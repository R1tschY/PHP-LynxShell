<?php

$opt = new CmdlnOptions($args);
$opts = $opt->getArguments();

if (count($opts) == 0) {
  lputs('PHP version: '.phpversion().PHP_EOL.PHP_EOL);

  $a = ini_get_all(null, false);
  foreach ($a as $key => $value) {
    lputs(str_pad($key, 30).' : '.print_var($value));
  }
} else {
  $a = ini_get_all(null, false);
  foreach ($opts as $o) {
    if (array_key_exists($o, $a)) {
      lputs(str_pad($o, 30).' : '.print_var($a[$o]));
    } else {
      Answer::addOutput('e', str_pad($o, 30).' : setting does not exist'.PHP_EOL);
    }
  }
}

?>
