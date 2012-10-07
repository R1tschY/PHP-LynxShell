<?php

if (count($args) == 2) {
  Answer::addOutput('o', 'usage: ' . $args[0].' seconds');
  return ;
}

sleep($args[1]);

?>
