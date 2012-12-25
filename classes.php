<?php 
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */

include('config.php');

////////////////////////////////////////////////////////////////////////////////
//   Debug Utils
////////////////////////////////////////////////////////////////////////////////
function var_dup($var) {
  if (is_bool($var)) {
    $dup = $var?'TRUE':'FALSE';
  } else {
    $dup = print_r($var, true);
  }

  return $dup.'(type:'.gettype($var).')';
}

function print_var($var) {
  if (is_null($var)) {
    $dup = 'NULL';
  } else if (is_scalar($var)) {
    if (is_bool($var)) {
      $dup = $var?'TRUE':'FALSE';
    } else if (is_int($var)) {
      $dup = strval($var);
    } else if (is_float($var)) {
      $dup = strval($var);
    } else if (is_string($var)) {
      $dup = '"'.$var.'"';
    } else if (is_array($var)) {
      $dup = print_r($var, true);
    }
  } else {
    $dup = print_r($var, true);
  }

  return $dup;
}

////////////////////////////////////////////////////////////////////////////////
//   File/Size Utils
////////////////////////////////////////////////////////////////////////////////

function return_bytes($val) {
  $val = trim($val);
  $last = strtolower($val[strlen($val)-1]);
  switch($last) {
  case 'g':
    $val *= 1024;
  case 'm':
    $val *= 1024;
  case 'k':
    $val *= 1024;
  }

  return $val;
}

function byte_size_string($val) {
  if ($val < 1024) {
    return $val . ' B';
  } else if ($val < 1024*1024) {
    return ($val / (1024*1024)) . ' kiB';
  } else if ($val < 1024*1024*1024) {
    return ($val / (1024*1024)) . ' MiB';
  } else {
    return ($val / (1024*1024*1024)) . ' GiB';
  }
}

////////////////////////////////////////////////////////////////////////////////
//   Error Handling
////////////////////////////////////////////////////////////////////////////////

function handle_error($no, $str, $file, $line) {
  switch ($no) {
  case E_USER_NOTICE:
  case E_RECOVERABLE_ERROR:
  case E_ERROR:
    $type = 'e';
    $name = 'error';
    break;
    
  case E_WARNING:
    $type = 'w';
    $name = 'warning';
    break;
  
  case E_STRICT:
  case E_DEPRECATED:
  case E_USER_DEPRECATED:
  case E_NOTICE:
    $type = 'n';
    $name = 'notice';
    break;
    
  default:
    $type = 'e';
    $name = 'unknown error';
  }

  Answer::addOutput($type, 'php '.$name.': '.strip_tags(htmlspecialchars_decode($str)).' in '.$file.'('.$line.")\n");
  
  if ($type == 'e') {
    exit();
  }
  
  return TRUE;
}

////////////////////////////////////////////////////////////////////////////////
//   Commandline Utils
////////////////////////////////////////////////////////////////////////////////

class CmdlnOptions {
  private $opts;
  private $lopts;
  private $args;
  
  public function __construct($args) {
    $this->opts = array();
    $this->lopts = array();
    $this->args = array();
    
    foreach ($args as $key=>&$opt) { // TODO: Fehlerbehandlung
      if ($opt[0] == '-') {
        if ($opt[1] == '-') {
          $this->lopts[substr($opt, 2)] = false;
        } else {
          $this->opts[substr($opt, 1)] = false;
        }
      } else {
        if ($key == 0) continue;
        $this->args[] = $opt;
      }
    }    
  }
  
  public function isOptionSet($short, $long) {
    if (!empty($short) && array_key_exists($short, $this->opts)) {
      return true;
    }
    if (!empty($long) && array_key_exists($long, $this->lopts)) {
      return true;
    }
    return false;    
  }
  
  public function getOption($short, $long) {
    if (array_key_exists($short, $this->opts)) {
      return $this->opts[$short];
    }
    if (array_key_exists($long, $this->lopts)) {
      return $this->lopts[$long];
    }
    return false;  
  }
  
  public function getArguments() {
    return $this->args;
  }
}

function parse_cmdln($argv) {
  $result = array();
  
  foreach ($argv as $key=>&$opt) {
    if ($opt[0] == '-') {
      if ($opt[1] == '-') {
        $result[substr($opt, 2)] = false;
      } else {
        $result[substr($opt, 1)] = false;
      }
    } else {
      if ($key == 0) continue;
      $result[] = $opt;
    }
  }
  // TODO: Fehlerbehandlung
  return $result;
}

////////////////////////////////////////////////////////////////////////////////
//   Input Utils
////////////////////////////////////////////////////////////////////////////////

function filter_arrayvalue_bool($array, $key, $default = FALSE) {
  if (array_key_exists($key, $array)) {
    $value = $array[$key];
    if (!empty($value)) {
      return ($value=='true')?true:false;
    }
  }  
  return $default;
}

function filter_arrayvalue_int($array, $key, $default = FALSE) {
  if (array_key_exists($key, $array)) {
    $value = $array[$key];
    $int = intval($value);
    if (is_numeric($value) && $int == $value) {
      return $int;
    }
  }
  
  return $default;
}

function filter_arrayvalue_str($array, $key, $default = FALSE) {
  if (array_key_exists($key, $array)) {
    $value = $array[$key];
    if (!empty($value)) {
      return strval($value);
    }
  }  
  return $default;
}

////////////////////////////////////////////////////////////////////////////////
//   String Utils
////////////////////////////////////////////////////////////////////////////////

function str_find_prefix($a, $b) {
  for ($ic=0; $ic<min(strlen($a),strlen($b)); $ic++) {
    if ($a[$ic] != $b[$ic]) break;
  }
  return substr($a, 0, $ic);
}

function str_find_suffix($a, $b) {
  $alen = strlen($a);
  $blen = strlen($b);
  for ($ic=0; $ic<min($alen, $blen); $ic++) {
    if ($a[$alen-$ic-1] != $b[$blen-$ic-1]) break;
  }
  return substr($a, $alen-$ic);
}

function strarray_find_prefix(&$data) {
  $prefix = $data[0];
  for ($i=1; $i<count($data) && !empty($prefix); $i++) {
    $prefix = str_find_prefix($prefix, $data[$i]);
  }
  return $prefix;
}

function strarray_remove_prefix(&$data, $prefix) {
  $cnt = strlen($prefix);
  foreach ($data as &$value) {
    $value = substr($value, $cnt);
  }
}

function strarray_remove_suffix(&$data, $suffix) {
  $cnt = strlen($suffix);
  foreach ($data as &$value) {
    $value = substr($value, 0, -$cnt);
  }
}

function is_prefix($a, $prefix) {
  $alen = strlen($a);
  $prelen = strlen($prefix);
  if ($alen < $prelen) return false;
  return (strcmp(substr($a, 0, $prelen), $prefix) == 0);  
}

function is_suffix($a, $suffix) {
  $alen = strlen($a);
  $suflen = strlen($suffix);
  if ($alen < $suflen) return false;
  return (strcmp(substr($a, 0, -$suflen), $suffix) == 0);  
}

////////////////////////////////////////////////////////////////////////////////
//   print_in_table
////////////////////////////////////////////////////////////////////////////////

function print_in_table(&$data, $maxlen) {
  if (count($data) == 0) return ;
  
  $cw = filter_arrayvalue_int($_POST, 'cw');
  if ($cw !== FALSE) {
    if ($cw < 10) $cw = 10;
    $colwidth = $maxlen + 2;
    $items = count($data);
    $cols = intval($cw / $colwidth);
    if ($cols < 1) $cols = 1;
    if ($cols > $items) $cols = $items;
    $rows = intval($items/$cols)+1;
//    Answer::addOutput('n', 'colw:'.var_dup($colwidth).'/'.var_dup($cw) .' i:'. var_dup($items).' cols:'. var_dup($cols).' rows:'. var_dup($rows));
  } else {
    $cols = 1;
  }
    
  if ($cols > 1) {    
    for ($i = 0; $i < $rows; $i++) {
      $line = '';
      for ($ii = 0; $ii < $cols; $ii++) {
        $j = $i*$cols+$ii; 
        if ($j >= $items) break;
        $line .= str_pad(filter_arrayvalue_str($data, $j, ' '), $colwidth);
      }
      if (!empty($line)) lputs($line);
    }
  } else {
    lputs(implode("\n", $data));
  }
}

////////////////////////////////////////////////////////////////////////////////
//   complete
////////////////////////////////////////////////////////////////////////////////

function _dirname($path) {
  $pos = strrpos($path, '/');
  if ($pos === false) {
    return '';
  } else {
    return substr($path, 0, $pos);
  }
}

function escape_spaces($path) {
  if (strstr($path, ' ') !== FALSE) {
    return '"'.$path.'"';
  } else {
    return $path;
  }
}

function complete($args) {
  $cmd = $args[1]; 
  $file = filter_arrayvalue_str($args, 2, '');
  
  // FIXME: escape * [] {}
   
  if ($cmd == 'cmd') {
    if ($file === FALSE) $file = '';
    $prefix = dirname(__FILE__).'/bin/';
    $candidates = glob($prefix.$file.'*.php');   
    strarray_remove_suffix($candidates, '.php');
    strarray_remove_prefix($candidates, $prefix);
    $prefix = '';
    
  } else {
    $prefix = _dirname($file);
  
    if ($cmd == 'dir') {
      $candidates = glob($file.'*', GLOB_MARK | GLOB_ONLYDIR);                        
    } else {
      $candidates = glob($file.'*', GLOB_MARK);
    }
    
    if (!empty($prefix)) {
      $prefix .= '/';
      strarray_remove_prefix($candidates, $prefix);
    }
  }
  
  if (count($candidates) < 1) {
    Answer::setStatus('NOT_FOUND');
    
  } elseif (count($candidates) == 1) {
    Answer::setResult(escape_spaces($prefix.$candidates[0]));
    
  } elseif (count($candidates) > 1) {
    Answer::setStatus('MORE_FOUND');
    Answer::setResult(escape_spaces($prefix.strarray_find_prefix($candidates)));
    
    $maxlen = 5;
    foreach ($candidates as $can) {
      $len = strlen($can);
      if ($len > $maxlen) $maxlen = $len;
    }    
    print_in_table($candidates, $maxlen);
  }
}

////////////////////////////////////////////////////////////////////////////////
//   History
////////////////////////////////////////////////////////////////////////////////
class History {
  
  public static function add($cmd) {
    if (!array_key_exists('history', $_SESSION)) {
      $_SESSION['history'] = array($cmd);
    } else {
      if (end($_SESSION['history']) != $cmd)
        $_SESSION['history'][] = $cmd;
    }
  }   
}

////////////////////////////////////////////////////////////////////////////////
//   Env
////////////////////////////////////////////////////////////////////////////////
class Env {
  private static $disable_functions;
  
  public static function init() {
    // home
    if (!array_key_exists('home', $_SESSION)) {  
      $home = getenv("HOME");
      if (empty($home)) {      
        $user_dir = ini_get('user_dir');
        if (!empty($user_dir)) {
          $_SESSION['home'] = $user_dir;
          
        } else {      
          $open_basedir = ini_get('open_basedir');
          if (!is_null($open_basedir)) {
            $t = explode(':', $open_basedir, 2);
            $_SESSION['home'] = $t[0];
          } else {
            $_SESSION['home'] = '';
          }
        }
      } else {
        $_SESSION['home'] = $home;
      }
    }    
    $_SESSION['home'] = rtrim($_SESSION['home'], '/');
    
    // disable_functions
    self::$disable_functions = explode(',', ini_get('disable_functions'));    
  }
  
  public static function areFunctionsAvailable() {
    return count(array_intersect(self::$disable_functions, func_get_args())) == 0;
  }
  
  public static function requireFunctions() {
    foreach (func_get_args() as $func) {
      if (function_exists($func) == FALSE) {
        Answer::addOutput('e', 'error: required function "'.$funcs.'" not available on this system');
        return FALSE;
      }
    }
    return TRUE;
  }
  
  public static function requireClasses() {
    foreach (func_get_args() as $class) {
      if (class_exists($class) == FALSE) {
        Answer::addOutput('e', 'error: required class "'.$class.'" not available on this system');
        return FALSE;
      }
    }
    return TRUE;
  }
  
  public static function requireExtensions() {
    $args = func_get_args();
    $exts = array_intersect(get_loaded_extensions(), $args);
    if (count($exts) != count($args)) {
      $diff = array_diff($exts, $args);
      foreach ($diff as $ext) {
        Answer::addOutput('e', 'error: required module "'.$ext.'" not available on this system');
      }
      return FALSE;
    }
    return TRUE;
  }
  
  public static function getHome() { return $_SESSION['home']; }
}

////////////////////////////////////////////////////////////////////////////////
//   Path Utils
////////////////////////////////////////////////////////////////////////////////
function expandPath($path) {
  $home = Env::getHome();
  if (empty($home)) $home = '/';

  if ($path[0] == '~') {
    $path = str_replace('~', $home, $path); // TODO: nur erstes Vorkommen austauschen
  }
  return $path;
}

function implodePath($path) {
  $home = Env::getHome();
  
  if (is_prefix($path, $home)) {
    $path = str_replace($home, '~', $path); // TODO: nur erstes Vorkommen austauschen
  }
  return $path;
}

////////////////////////////////////////////////////////////////////////////////
//   ShellSession
////////////////////////////////////////////////////////////////////////////////
class ShellSession {
  private $cwd;
  
  private function __construct($cwd) {
    $this->cwd = $cwd;
  }
  
  public function getPrompt() {
    return Authorization::get_user().'@'.$_SERVER['SERVER_NAME'].':'.
      implodePath($this->cwd).'$ ';
  }
  
  public function getCwd() {
    return $this->cwd;
  }
   
  public function setCwd($value) {
    $path = realpath($value);
    if (!$path || !is_dir($path)) { 
      return false;
    } else {
      $this->cwd = $path;
      return true;
    }
  }
  
  static public function get() {
    if (array_key_exists('shell', $_SESSION)) {
      $shell = &$_SESSION['shell'];
      if (is_dir($shell->cwd)) {
        if (!chdir($shell->cwd)) {
          fatal_error('kann Arbeitsverzeichniss nicht setzen');
        }
      } else {
        fatal_error('aktuelles Arbeitsverzeichnis ('.$shell->cwd.') nicht vorhanden');
      }
    } else {
      $cwd = getcwd();
      if (!$cwd) {
        fatal_error('kein Zugriff auf aktuelles Arbeitsverzeichniss möglich');
      }
      $_SESSION['shell'] = new ShellSession($cwd); 
      $shell = &$_SESSION['shell']; 
    }
    return $shell;
  }
}

////////////////////////////////////////////////////////////////////////////////
//   Answer
////////////////////////////////////////////////////////////////////////////////
class Answer {
  private static $answer;
  private static $outnr;
  
  public static function init() {
    self::$outnr = 0;
  }
  
  public static function addOutput($channel, $msg) {
    self::$answer[self::$outnr] = array('c' => $channel, 'm' => $msg);
    self::$outnr++;
  }
  
  public static function setStatus($status) {
    self::$answer['status'] = $status;
  }
  
  public static function setResult($result) {
    self::$answer['result'] = $result;
  }
  
  public static function send() {
    if (array_key_exists('shell', $_SESSION)) {
      self::$answer['shell'] = $_SESSION['shell']->getPrompt();
    }
    
    if (!array_key_exists('status', self::$answer)) {
      self::$answer['status'] = 'NO_ERROR';
    }
    
    header('Content-type: application/json');
    //ob_start('ob_gzhandler');
    echo json_encode(self::$answer);
    //ob_end_flush();
    exit();
  }
}

function lerror($msg) {
  Answer::addOutput('e', $msg.PHP_EOL);
  exit();  
}

function fatal_error($msg) {
  Authorization::logout();
  
  Answer::addOutput('e', $msg.PHP_EOL);
  Answer::setStatus('FATAL_ERROR');
  exit();  
}

function lwarning($str) {
  Answer::addOutput('w', $str.PHP_EOL);
}

function lputs($str) {
  Answer::addOutput('o', $str.PHP_EOL);
}

function lfputs($c, $str) {
  Answer::addOutput($c, $str.PHP_EOL);
}

////////////////////////////////////////////////////////////////////////////////
//   Authorization
////////////////////////////////////////////////////////////////////////////////
class Authorization {

  private static $auth;
  private static $user;
  private static $wrongpwd = false;
  public static $userpasswd = array();
  public static $init = false;

  public static function createpasswd($passwd) {
    return hash('sha256', $passwd);
  }
  
  public static function checkpasswd($user, $passwd) {
    return array_key_exists($user, self::$userpasswd) &&
      (self::$userpasswd[$user] == self::createpasswd($passwd));
  }

  public static function init($name, $timeout=null) {
    if (self::$init)
      return;

    if (!is_null($timeout))
      session_set_cookie_params($timeout);

    session_name($name);
    session_start();
    self::$init = true;

    if (self::$auth = array_key_exists('auth', $_SESSION)) {
      self::$user = $_SESSION['user'];
    } else {
      if (array_key_exists('user', $_POST) && array_key_exists('pwd', $_POST)) {
        $user = $_POST['user'];
        if (self::checkpasswd($user, $_POST['pwd'])) {
          $_SESSION['auth'] = self::$auth = true;
          $_SESSION['user'] = self::$user = $user;
        } else {
          self::$wrongpwd = true;
        }
      }
    }
  }

  public static function logout() {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params['path'],
                $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
  }

  public static function is_auth() {
    return self::$auth;
  }

  public static function got_wrongpwd() {
    return self::$wrongpwd;
  }

  public static function get_user() {
    return self::$auth ? self::$user : '';
  }
}

?>
