<?php

if (count($args) != 2) {
  lerror('usage: ' . $args[0].' seconds');
}

sleep($args[1]);

?>
