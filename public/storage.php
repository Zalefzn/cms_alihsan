<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/storage/#', '', $path);
$path = urldecode($path);
$base = realpath(__DIR__ . '/../storage/app/public');
$full = realpath($base . '/' . $path);
if ($full === false || strpos($full, $base) !== 0 || !is_file($full)) {
    http_response_code(404);
    header('Content-Type: text/plain');
    exit('Not found');
}
$mime = function_exists('mime_content_type') ? mime_content_type($full) : 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($full));
header('Cache-Control: public, max-age=31536000, immutable');
readfile($full);
