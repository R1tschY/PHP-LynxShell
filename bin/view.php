<?php

$opt = new CmdlnOptions($args);
$files = $opt->getArguments();
Answer::setStatus('ERROR');

if (count($files) != 1) {
  lerror('usage: view [FILE]');
}

$file = realpath($files[0]);
$doc_root = $_SERVER['DOCUMENT_ROOT'];
if (!is_prefix($file, $doc_root)) {
  lerror($file.' is not a web document');
}

$protokol = (empty($_SERVER['HTTPS'])) ? 'http://' : 'https://';
$port = ($_SERVER["SERVER_PORT"] != "80") ? ':'.$_SERVER["SERVER_PORT"] : '';

$result = $protokol.$_SERVER["SERVER_NAME"].$port.substr($file, strlen($doc_root));

Answer::setStatus('NO_ERROR');
Answer::setResult($result);
lputs($result);

?>