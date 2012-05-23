<?php

if (trim($args[1]) == '') {
  Answer::addOutput('o', $args[0].': No input file');
  return ;
}

if (file_exists($args[1])) {
  Answer::addOutput('o', file_get_contents($args[1]));
} else {
  Answer::addOutput('o', $args[0].': '.$args[1].': No such file or directory');
}

?>
