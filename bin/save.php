<?php

if (!array_key_exists('input', $_POST) || !array_key_exists('file', $_POST)) {
  Answer::addOutput('e', 'Befehl nicht anwendbar');
  Answer::send();
  return ;
}

// TODO: file absichern

if (file_put_contents($_POST['file'], $_POST['input'])===FALSE) {
  Answer::addOutput('e', 'Fehler beim Speichern');
  Answer::send();
  return ;
} else {
  Answer::addOutput('o', 'gespeichert');
  Answer::send();
  return ;
}


?>