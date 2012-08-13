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

// filter 'eval' commands:
if (!preg_match('#^[a-zA-Z0-9_-]+$#', $cmd)) {
  Answer::addOutput('o', $cmd.': command not found');
  Answer::send();
}

if (empty($cmd)) {
  Answer::addOutput('o', '');
  Answer::send();
}

// expand arguments
$tmp = array($args[0]);
foreach (array_slice($args, 1) as $arg) {
  $all = glob($arg);
  if (!empty($all)) {
    $tmp = array_merge($tmp, $all);
  } else {
    $tmp[] = $arg;
  } 
}
$args = $tmp;

include("commands.php");

register_shutdown_function('Answer::send');
Commands::call($cmd, $args);

?>
