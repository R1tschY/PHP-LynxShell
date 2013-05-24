<?php

$opt = new CmdlnOptions($args);
$passwd = $opt->getArguments();

if (count($passwd) != 1) {
  lerror('usage: passwd [PASSWORD]');
}

lputs(Authorization::createpasswd($passwd[0]));

?>
