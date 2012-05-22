<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */
 
include("classes.php");

Answer::init();
set_error_handler('handle_error', E_ALL | E_STRICT);

// Autorisierung durchführen
Authorization::init('s');
if (!Authorization::is_auth()) {
  Answer::addOutput('o', 'Nicht autorisiert!');
  Answer::setStatus('NOT_AUTHORIZED');
  Answer::send();
}

// System initialisieren
Env::init();

// Eingabewerte parsen
$request = array();
if (!array_key_exists('cmd', $_POST)) {
  if (!array_key_exists('cmd', $_GET)) {
    Answer::addOutput('o', 'Fehler: cmd-Parameter fehlt');
    Answer::send();
  }
  $cmdln = trim($_GET['cmd']);
} else {
  $cmdln = trim($_POST['cmd']);
}
$intern = filter_arrayvalue_bool($_POST, 'i', false);
$clientwidth = filter_arrayvalue_int($_POST, 'cw', -1);

// Shell Session holen
$shell = ShellSession::get();

// arguments
$args = preg_split("/[\s]+/", $cmdln);
$cmd = &$args[0];

// buildin commands
switch ($cmd) {
case 'login': 
  Answer::addOutput('o', login());
  Answer::send();
  
case 'logout':
case 'exit':
  if (!$intern) History::add($cmdln);
  Authorization::logout();
  Answer::addOutput('o', 'logout');
  Answer::setStatus('NOT_AUTHORIZED');
  Answer::send();
  
case 'cd':
  if (empty($args[1])) $args[1] = '~';
  $path = expandPath($args[1]);
  if (!$shell->setCwd($path)) {  
    Answer::addOutput('e', $path.' ist kein gültiges Verzeichnis');
  }
  if (!$intern) History::add($cmdln);
  Answer::send();
  
case 'pwd':
  if (!$intern) History::add($cmdln);
  Answer::addOutput('o', $_SESSION['shell']->getCwd());
  Answer::send();
  
case '':
  Answer::addOutput('o', '');
  Answer::send();
  
case 'history':
  if (!$intern) History::add($cmdln);
  Answer::addOutput('o', implode("\n", $_SESSION['history']));
  Answer::send(); 
  
case 'complete':
  if (!$intern) History::add($cmdln);
  complete($args);  
}

// filter 'eval' commands:
if (!preg_match('#^[a-zA-Z0-9_-]+$#', $cmd)) {
  Answer::addOutput('o', $cmd.': command not found');
  Answer::send();
}

// Befehl in bin/ Ordner finden
$cmdfile = dirname(__FILE__).'/bin/'.$cmd.'.php';
if (file_exists($cmdfile)) {
  include($cmdfile);
  if (!$intern) History::add($cmdln);
  Answer::send();
  return ;
} else {
  Answer::addOutput('o', $cmd.': command not found');
  Answer::send();
  return ;
}

?>
