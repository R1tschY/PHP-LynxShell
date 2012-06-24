<?php

if (!Env::areFunctionsAvailable('posix_getgroups', 'posix_getgrgid')) {
  Answer::addOutput('e', 'Function posix_getgroups or posix_getgrgid not available on this system.');
  return ;
}

$str = '';
foreach(posix_getgroups() as $group) {
  $g = posix_getgrgid($group);
  $str .= $g['name'];
}

Answer::addOutput('o', $str);

?>
