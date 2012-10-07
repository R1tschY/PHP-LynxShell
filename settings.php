<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */ 

include("classes.php");

Authorization::init('s');
if (Authorization::is_auth()) {
  Env::init();
  $shell = ShellSession::get();
  $shellname = $shell->getPrompt();
} else {
  $shellname = '>';
}
?>

<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">

  <title>PHP Lynx Shell</title>
  <meta name="description" content="">
  <meta name="author" content="r1tschy">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <link rel="stylesheet" href="css/style.css">
  <link href='http://fonts.googleapis.com/css?family=Droid+Sans+Mono' rel='stylesheet' type='text/css'>
</head>

<body>
  <table class="login_bgd">
    <td class="hbox">
    <div class="login_dialog">
      <h1>Lynx Shell Settings</h1>
      <br />
	    <form class="settings" autocomplete="off">
	      <table>
	      <tr>
          <td><label for="user">User:</label></td>
          <td><input type="text" name="user" id="user" class="input" /></td>
        </tr>
        <tr>
          <td><label for="pwd">Password:</label></td>
          <td><input type="password" name="pwd" id="pwd" class="input" /></td>
        </tr>
        <tr>
          <td><label for="pwd2">Confirm Password:</label></td>
          <td><input type="password" name="pwd2" id="pwd2" class="input" /></td>
        </tr>
        <tr>
          <td><label for="mod">Modus create files:</label></td>
          <td><input type="text" name="mod" id="mod" class="input" /></td>
        </tr>
        </table>
      </form>
    </div>
    </td>
  </table>
  
  <!-- Grab Google CDN's jQuery, with a protocol relative URL -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

  <!-- scripts concatenated and minified via ant build script-->
  <script defer src="js/plugins.js"></script>
  <script defer src="js/script.js"></script>
  <!-- end scripts--> 
</body>
</html>