<?php
// Set the Content-Type header to video/mp2t
header('Content-Type: video/mp2t');

// Prevent caching to ensure the browser always requests the latest stream data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// You might also want to set Content-Disposition for some scenarios,
// though for a direct stream it's less critical unless you want to suggest download filename.
// header('Content-Disposition: inline; filename="stream.ts"');

// --- Your streaming logic goes here ---

// Example: Serving a static .ts file as a stream
// Make sure the path to your .ts file is correct
/*
$file = $_GET['video'];

if (file_exists($file)) {
    // Optional: Set Content-Length if you know the file size and want to hint to the client
    // header('Content-Length: ' . filesize($file));

    // Read the file and output its contents
    readfile($file);
} else {
    // Handle file not found error
    header('HTTP/1.0 404 Not Found');
    echo 'File not found.';
}
*/
// Example: If you are dynamically generating or proxying a stream,
// you would output the data in chunks here.
// For instance, if you're fetching from another URL:
ini_set('user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0) Gecko/20100101 Firefox/42.0');
ini_set('memory_limit', '6000M');
ini_set('max_execution_time', 3600);

if ($stream = fopen('http://applazer.online:80/marcoaurelio28/97638250/188727.ts', 'r')) {
    // print all the page starting at the offset 10
    echo stream_get_contents($stream, -1, 10);

    fclose($stream);
}

die(var_dump(__FILE__, __LINE__));

$remoteStreamUrl = 'http://applazer.online:80/marcoaurelio28/97638250/188727.ts';
$stream = fopen($remoteStreamUrl, 'r');
if ($stream) {
    while (!feof($stream)) {
        echo fread($stream, 8192); // Read and output in chunks
        flush(); // Flush the output buffer
    }
    fclose($stream);
} else {
    header('HTTP/1.0 500 Internal Server Error');
    echo 'Could not open remote stream.';
}

exit; // Important: terminate the script after sending the stream