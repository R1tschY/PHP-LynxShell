<?php

Authorization::$userpasswd = array('admin'=>'8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918');

// Limit for downloading files
define("DOWNLOAD_LIMIT", 2*1024); // 1kB/s

// Size of chunks when downloading files
define("DOWNLOAD_CHUNK_SIZE", 1024); // 100kB

// buffer size for write/read files
define("BUFFER_SIZE", 65536);

// Permission mode for new files/directories
define("PERMISSIONS", 0777);


return ;
?>

