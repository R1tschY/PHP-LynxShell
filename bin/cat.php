<?php

if (trim($args[1]) == '') {
  Answer::addOutput('stdout', $args[0].': No input file');
  return ;
}

if (file_exists($args[1])) {
  Answer::addOutput('stdout', file_get_contents($args[1]));
} else {
  Answer::addOutput('stdout', $args[0].': '.$args[1].': No such file or directory');
}

?>