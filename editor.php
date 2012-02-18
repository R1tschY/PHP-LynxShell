<?php
include("classes.php");

Authorization::init('s');
if (!Authorization::is_auth()) {
  exit('Nicht autorisiert!');
}

if (!array_key_exists('file', $_POST)) {
  exit('Keine Datei gewählt!');
}

?>

<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">

  <title>PHP LUX Editor</title>
  <meta name="description" content="">
  <meta name="author" content="r1tschy">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

  <!-- CSS: implied media=all -->
  <!-- CSS concatenated and minified via ant build script-->
  <link rel="stylesheet" href="css/style.css">
  <!-- end CSS-->

  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->
  
  <link href='http://fonts.googleapis.com/css?family=Droid+Sans+Mono' rel='stylesheet' type='text/css'>
</head>

<body>

  <div id="container">
    <header>

    </header>
    <div id="main" role="main">  
      <button id="save">Speichern</button><br />
      <iframe id="edit" width="100%" height="200" file="<?php echo urlencode($_POST['file']); ?>"></iframe>
      <pre id="log"></pre>
    </div>
  </div> <!--! end of #container -->


  <!-- JavaScript at the bottom for fast page loading -->

  <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/libs/jquery-1.6.2.min.js"><\/script>')</script>


  <!-- scripts concatenated and minified via ant build script-->
  <script defer src="js/plugins.js"></script>
  <script defer src="js/editor.js"></script>
  <!-- end scripts--> 
</body>
</html>
