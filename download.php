<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 *
 *
 * TODO:
 *   - multi range support
 *   - DownThemAll! gives Server-Error when restarting large file download
 *   - multi etag support
 *   - multi file support (with bounds)
 */
 
// Disable Error Logging
error_reporting(0);

include("classes.php");

function error_403() {
  header("Status: 403 Forbidden");
  header("Content-Type: text/html");
  
  echo "
<html><head>
<title>403 Forbidden</title>
</head><body>
<h1>Forbidden</h1>
<p>You do not have permission to access this document.</p>
</body></html>";
  exit();
}

function error_404($path) {
  header("Status: 404 Not Found");
  header("Content-Type: text/html");

  echo "
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested document $path was not found on this server.</p>
</body></html>";
  exit();
}

function error_412() {
  header("Status: 412 Precondition Failed");
  header("Content-Type: text/html");

  echo '
<html><head>
<title>412 Precondition Failed</title>
</head><body>
<h1>The precondition on the request for the document evaluated to false.</h1>
</body></html>';
  exit();
}
    
function error_416($filesize) {
  header("Status: 416 Requested range not satisfiable");
  header("Content-Type: text/html");
  header("Content-Range: bytes */".$filesize);

  echo '
<html><head>
<title>416 Requested range not satisfiable.</title>
</head><body>
<h1>Requested range not satisfiable</h1>
</body></html>';
  exit();
}
 
function get_mine_type($path) {
  $finfo = finfo_open(FILEINFO_MIME | FILEINFO_CONTINUE);
  $mine_type = finfo_file($finfo, $path);
  finfo_close($finfo);
  return $mine_type;
}

function parse_qvalues($qvalue_str) {
  $x = explode(',', $qvalue_str);
  $lastq = 0.0;
  $q = array();
  foreach (array_reverse($x) as $value) {
    if (preg_match("/(.+);q=([\.0-9]+)/i", $value, $matches)) {
      $lastq = $q[$matches[1]] = floatval($matches[2]);
    } else {
      $q[$value] = $lastq;
    }
  }

  arsort($q, SORT_NUMERIC);
  
  $r = array();
  foreach ($q as $value => $qvalue) {
    if ($qvalue > 0.0) {
      $r[] = $value;
    } else {
      break;
    }
  }
  
  return $r;
}

function file_changed($etag, $mtime) {
  $range_str = filter_arrayvalue_str($_SERVER, "HTTP_RANGE");
  if ($range_str === FALSE) return true;
  
  $etag = '"'.$etag.'"';
  
  // Check If-Range
  $if_range = filter_arrayvalue_str($_SERVER, 'HTTP_IF_RANGE');
  if ($if_range !== FALSE) {
    return ($if_range != $etag && strtotime($if_range) != $mtime);
  }
    
  // Check If-Unmodified-Since and Unless-Modified-Since
  $if_unmodified = filter_arrayvalue_str($_SERVER, 'HTTP_IF_UNMODIFIED_SINCE');
  $if_modified = filter_arrayvalue_str($_SERVER, 'HTTP_UNLESS_MODIFIED_SINCE');
  
  if ($if_modified !== FALSE	&& $if_unmodified === FALSE)
    $if_unmodified = $if_modified;
    
  if ($if_unmodified !== FALSE && strtotime($if_unmodified) != $mtime) {
    error_412();
  }
  
  // Check If-Match
  $if_match = filter_arrayvalue_str($_SERVER, 'HTTP_IF_MATCH', $etag);  
  if ($if_match !== FALSE && $if_match != $etag) {
    error_412();
  }
  
  return false;
}

function get_range($range_str, $filesize) {
  $success = preg_match("/^bytes=([0-9]*)-([0-9]*)$/", $range_str, $results);
  if ($success !== 1) {
    $low = $high = FALSE;
  } else {
    $low = $results[1];
    $high = $results[2];
  }
  
  if (empty($low)) {
    $low = 0;
  } else {
    $low = intval($low);
  }
  
  if (empty($high)) {
    $high = $filesize - 1;
  } else {
    $high = intval($high);
  }
  
  if ($low > $high || $high >= $filesize) {
    error_416($filesize);
  }
  
  return array('high'=>$high, 'low'=>$low);
}

///////////////////////////////////////////////////////////////////////////////
// init

Authorization::init('s');
if (!Authorization::is_auth()) {
  error_403();
}

// get shell context (e.x. current directory)
ShellSession::get();

////////////////////////////////////////////////////////////////////////////////
// Get arguments

if (!array_key_exists('file', $_POST)) {
  if (!array_key_exists('file', $_GET)) {
    error_404('(unknown)');
  }
  $path = $_GET['file'];
} else {
  $path = $_POST['file'];
}

///// Downloadable? /////
$stat = @stat($path);
if (!is_file($path) || !is_readable($path) || $stat === FALSE) {
  error_404($path);
}

///// Range Request /////
$etag = $stat['mtime'].':'.$stat['size'];
header('ETag: "'.$etag.'"');

if (!file_changed($etag, $stat['mtime'])) {
  $range = get_range($_SERVER['HTTP_RANGE'], $stat['size']);
  $start = $range['low'];
  $end = $range['high'];
  
  header('Status: 206 Partial Content');
  header('Content-Range: bytes '.$start.'-'.$end.'/'.$stat['size']);
  header('Content-Length: '.($end - $start + 1));
} else {
  $start = 0;
  $end = $stat['size'] - 1;
  
  header("Content-Length: ".$stat['size']);
}

///// Minetype /////
$mine = get_mine_type($path);
if ($mine === FALSE) {
  $mine = 'application/octet-stream';
}
header("Content-Type: ".$mine);

///// Checksum /////
$digest = filter_arrayvalue_str($_SERVER, 'HTTP_WANT_DIGEST');
if ($digest !== FALSE) {
  // parse qvalue list
  $use = parse_qvalues($digest);
  
  // get available digest algos
  $algos = hash_algos();
  $algos[] = 'sha';

  foreach ($use as $value) {
    $l = strtolower($value);
    if (in_array($l, $algos)) {
      if ($l == 'sha') $l = 'sha1';
      
      $hash = base64_encode(hash_file($l, $path, true));
      
      if ($l == 'md5')
        header('Content-MD5: '.$hash);
      header('Digest: '.$l.'='.$hash);
      
      break;
    }
  }
}

///// Cache /////
header('Last-Modified: '.gmdate(DATE_RFC1123, $stat['mtime']));
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

///// Extra Parameter /////

// force download window in browser
header('Content-Disposition: attachment; filename="'.basename($path).'"');

// we accept continuable downloads
header('Accept-Ranges: bytes');

// we transfer binary
header('Content-Transfer-Encoding: binary');

// Send Header
ob_clean();
flush();

///// Prepare Downloading /////

// exit output buffer
while (ob_get_level()) {
  ob_end_clean();
} 

// Clear timeout
set_time_limit(0);

// compute wait time betweeen chunks [usec]
$tpc = (DOWNLOAD_CHUNK_SIZE * 1000 * 1000) / DOWNLOAD_LIMIT;

///// Download /////

// Open file
$f = fopen($path, "rb");

// Jump to start
if ($start != 0) {
  fseek($f, $start, SEEK_SET);
}

// Download
$left = $end - $start + 1;
while ($left > DOWNLOAD_CHUNK_SIZE) {
  echo fread($f, DOWNLOAD_CHUNK_SIZE);
  $left -= DOWNLOAD_CHUNK_SIZE;
  flush();
  if (DOWNLOAD_LIMIT > 0) usleep($tpc);
}
echo fread($f, $left);
flush();

fclose($f); 
 
// Exit successfully. We could just let the script exit
// normally at the bottom of the page, but then blank lines
// after the close of the script code would potentially cause
// problems after the file download. 
exit();

?>

