<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 *
 * TODO
 *  - PUT Requests
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

// Shell Session holen
$shell = ShellSession::get();

// Check system
if (ini_get('file_uploads') == 0) {
  Answer::addOutput('e', 'No file uploads allowed on this server!');
  Answer::send();
}

// Get checksums
if (array_key_exists('HTTP_X_CHECKSUM', $_SERVER)) {
  $checksums = explode(',', $_SERVER['HTTP_X_CHECKSUM']);
  if (count($checksums) != count($_FILES)) {
    Answer::addOutput('w', 'Number of checksums ('.count($checksums).') is not number of files ('.count($_FILES).') uploaded.');
    $checksums = FALSE;
  }
} else {
  $checksums = FALSE;
}

// Save uploaded files
$i = -1;
foreach ($_FILES as &$file) {
  $i++;
  if ($file['error'] == UPLOAD_ERR_OK) {
    $path = $file['name'];
    
    if (!is_writable(dirname($path))) {
      Answer::addOutput('e', $file['name'].': Upload directory is not writable.');
      continue;
    }
    
    if (file_exists($path)) {
      Answer::addOutput('e', $file['name'].': Uploaded file exists already.');
      continue;
    }
    
    if ($checksums !== FALSE) {
      if (hash_file('crc32', $file['tmp_name']) != $checksums[$i]) {
        Answer::addOutput('w', $file['name'].': Checksum check failed.');
      }
    }
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
      Answer::addOutput('o', $file['name'].': Upload was successful.');
    } else {
      Answer::addOutput('e', $file['name'].': Uploaded file is corrupted.');
      continue;
    }
  } else {
    switch ($file['error']) {
    case UPLOAD_ERR_INI_SIZE:
      $error = 'File is to big: '.byte_size_string($file['size']).'. On the server upload file size is limited to '.
               byte_size_string(return_bytes(ini_get('upload_max_filesize')).'.');
      break;
      
    case UPLOAD_ERR_FORM_SIZE:
      $error = 'File is to big: '.byte_size_string($file['size']).'. File size is more then the max filesize definied in form.';
      break;
      
    case UPLOAD_ERR_PARTIAL:
      $error = 'Partial file upload.';
      break;
      
    case UPLOAD_ERR_NO_FILE:
      $error = 'No file is uploaded.';
      break;
      
    default:
      $error = 'Unknown error code: '. $file['error'] .'.';
      break;
    }
    Answer::addOutput('e', $file['name'].': '.$error);
  } 
}

Answer::send();
 
?>
