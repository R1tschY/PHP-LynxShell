<?php

if (count($args) != 2) {
  lputs('usage: ' . $args[0].' seconds');
  return ;
}

sleep($args[1]);

?>
