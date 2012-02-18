<?php
/*  PHP LynxShell
 *   - PHP command line shell 
 *  Copyright (C) 2012 Richard Liebscher
 *
 *  License: GNU General Public License Version 3
 */
 

include("classes.php");

Authorization::init('s');

?>

<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">

  <title>PHP LynxShell</title>
  <meta name="description" content="">
  <meta name="author" content="r1tschy">
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <link rel="stylesheet" href="css/style.css">
  <link href='http://fonts.googleapis.com/css?family=Droid+Sans+Mono' rel='stylesheet' type='text/css'>
</head>

<body>
  <div id="container">
    <header>

    </header>
    <div id="main" role="main">  
<?php
if (!Authorization::is_auth()) {
	echo '
<form id="login" autocomplete="off">
  <fieldset>
    <input type="hidden" name="cmd" value="login">
    <pre><label for="user">username:</label><input type="text" name="user" id="user" /></pre>
    <pre id="pwdline" style="display:none"><label for="pwd">password:</label><input type="password" name="pwd" id="pwd" /></pre>
  </fieldset>
</form>';
}

?>
      <form action="javascript:void(0)" id="shell" autocomplete="off" <?php if (!Authorization::is_auth()) {echo 'style="display:none"';} ?>>
        <fieldset>
          <pre><label for="cmdln" id="shellname"><?php 
            if (Authorization::is_auth()) {echo $_SESSION['shell']->getPrompt();}
          ?></label><input name="cmd" type="text" id="input" /></pre>
        </fieldset>
      </form>
    </div>
    <footer>

    </footer>
  </div> 

  <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/libs/jquery-1.6.2.min.js"><\/script>')</script>

  <!-- scripts concatenated and minified via ant build script-->
  <script defer src="js/plugins.js"></script>
  <script defer src="js/script.js"></script>
  <!-- end scripts--> 
</body>
</html>
