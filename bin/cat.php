<?php

if (trim($args[1]) == '') {
  lerror('o', $args[0].': No input file');
}

if (file_exists($args[1])) {
  lputs(file_get_contents($args[1]));
} else {
  lputs($args[0].': '.$args[1].': No such file or directory');
}

?>
