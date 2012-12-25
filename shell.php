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
register_shutdown_function('Answer::send');

// Eingabewerte parsen
$request = array();
if (!array_key_exists('cmd', $_POST)) {
  if (!array_key_exists('cmd', $_GET)) {
    lerror('Fehler: cmd-Parameter fehlt');
  }
  $cmdln = trim($_GET['cmd']);
} else {
  $cmdln = trim($_POST['cmd']);
}

if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() == 1) {
  $mqs = strtolower(ini_get('magic_quotes_sybase'));
  if (empty($mqs) || $mqs == 'off') {
    $cmdln = stripslashes($cmdln);
  } else {
    $cmdln = str_replace("''", "'", $cmdln);
  }
}

$intern = filter_arrayvalue_bool($_POST, 'i', false);
$clientwidth = filter_arrayvalue_int($_POST, 'cw', -1);

// Shell Session holen
$shell = ShellSession::get();

// arguments
preg_match_all('/"(?:\\\\.|[^\\\\"])*"|\S+/', $cmdln, $matches);
$args = array_slice($matches[0], 1);
$cmd = $matches[0][0];
foreach ($args as &$arg) {
  if ($arg[0] == '"') {
    if (substr($arg, -1) == '"') {
      $arg = substr($arg, 1, -1);
    } else {
      lerror('missing closing quote');
    }
  } elseif (substr($arg, -1) == '"') {
    lerror('missing opening quote');
  }
}

// filter 'eval' commands:
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $cmd)) {
  lerror($cmd.': command not found');
}

if (empty($cmd)) {
  lputs('');
  exit();
}

// expand arguments
$tmp = array($cmd);
foreach ($args as $a) {
  $all = glob($a);
  if (!empty($all)) {
    $tmp = array_merge($tmp, $all);
  } else {
    $tmp[] = $a;
  }
}
$args = $tmp;

include("commands.php");

Commands::call($cmd, $args);

?>
