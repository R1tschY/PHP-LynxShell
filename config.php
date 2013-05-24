<?php

Authorization::$userpasswd = array('admin'=>'|_1oX:307b83311f95ddbd1c2b50e0a8e791614511572d3e2cd739aa229afb7101675b');

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

