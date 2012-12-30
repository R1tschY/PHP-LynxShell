<?php

Env::requireFunctions('posix_getgroups', 'posix_getgrgid') or exit();

$str = '';
foreach(posix_getgroups() as $group) {
  $g = posix_getgrgid($group);
  $str .= $g['name'];
}

lputs($str);

?>
