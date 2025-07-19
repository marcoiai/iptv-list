<?php
ini_set('memory_limit', '6000M');
//$stream = fopen('php://http', 'r+b');
//$output = readfile("http://127.0.0.1:9090/live?listen.mp4");
header('Content-Type: video/mp4');

// Prevent caching to ensure the browser always requests the latest stream data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$stream = fopen('http://127.0.0.1:9099/live.mp4', 'rb');

while (feof($stream) === false) {
    echo '[-------- CHUNK --------]' . PHP_EOL;

    var_dump(stream_get_meta_data($stream));

    //echo fread($stream, 2048);
}