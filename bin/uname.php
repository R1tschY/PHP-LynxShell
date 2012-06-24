<?php

if (!Env::areFunctionsAvailable('posix_uname')) {
  Answer::addOutput('e', 'Function posix_uname not available on this system.');
  return ;
}

if (count($args) < 2) {
  $args[] = '-s';
}

$opt = new CmdlnOptions($args);
$a = $opt->isOptionSet('a', 'all');
$s = $a || $opt->isOptionSet('s', 'kernel-name');
$n = $a || $opt->isOptionSet('n', 'nodename');
$r = $a || $opt->isOptionSet('r', 'kernel-release');
$v = $a || $opt->isOptionSet('v', 'kernel-version');
$m = $a || $opt->isOptionSet('m', 'maschine');

$uname = posix_uname();
$result = array();

if ($s) {
  $result[] = $uname['sysname'];
}

if ($n) {
  $result[] = $uname['nodename'];
}

if ($r) {
  $result[] = $uname['release'];
}

if ($v) {
  $result[] = $uname['version'];
}

if ($m) {
  $result[] = $uname['machine'];
}

Answer::addOutput('o', implode(' ', $result));

?>
