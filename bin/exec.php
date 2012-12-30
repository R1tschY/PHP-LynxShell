<?php

if (count($args) < 2) {
  lerror('usage: ' . $args[0].' command');
}

Env::requireFunctions('shell_exec');

Answer::addOutput('o', shell_exec(implode(" ", array_slice($args, 1))));

?>
