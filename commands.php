<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */

function shell_login($args) {
  Answer::addOutput('o', 
    'Welcome at Lynx Shell @ '.$_SERVER['SERVER_NAME']."\n".
    'Server: '.$_SERVER['SERVER_SOFTWARE']."\n".
    'PHP: '.phpversion());
    
  Answer::setResult(array(
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_file_uploads' => ini_get('max_file_uploads'),
    'post_max_size' => ini_get('post_max_size'),
  ));
}

function logout($args) {
  Authorization::logout();
  Answer::addOutput('o', 'logout');
  Answer::setStatus('NOT_AUTHORIZED');
}

function cd($args) {
  if (empty($args[1])) $args[1] = '~';
  $path = expandPath($args[1]);
  
  global $shell;
  if (!$shell->setCwd($path)) {  
    Answer::addOutput('e', $path.' ist kein gültiges Verzeichnis');
  }
}

function pwd($args) {
  global $shell;
  Answer::addOutput('o', $shell->getCwd());
}

function history($args) {
  Answer::addOutput('o', implode("\n", $_SESSION['history']));
}

function echo__($args) {
  Answer::addOutput('o', implode(" ", array_slice($args, 1)));
}

class Commands {
  static private $aliasies = array();
  static private $funcs = array(
    'login' => 'shell_login',
    'exit' => 'logout',
    'logout' => 'logout',
    'cd' => 'cd',
    'pwd' => 'pwd',
    'history' => 'history',
    'complete' => 'complete',
    'echo' => 'echo__'
  );

  static public function register($cmd, $func) {
    if (is_callable($func) && !array_key_exists($cmd, self::$funcs)) {
      self::$funcs[$cmd] = $func;
    }
  }

  static public function call($cmd, $args) {
    // if function exits
    // if no alias exits
   
    if (array_key_exists($cmd, self::$funcs)) {
      $funcname = self::$funcs[$cmd];
      $funcname($args);

    } else {    
      $cmdfile = dirname(__FILE__).'/bin/'.$cmd.'.php';
      if (file_exists($cmdfile)) {
        include($cmdfile);
      } else {
        Answer::addOutput('o', $cmd.': command not found');
        return ;
      }
    }
    
    global $intern;
    global $cmdln;
    if (!$intern) History::add($cmdln); 
  }
}

?>
