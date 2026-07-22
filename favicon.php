<?php
/**
 * Serves the site favicon for browsers that request /favicon.ico
 */
require_once __DIR__ . '/inc/functions.php';

$favicon = getSiteFavicon();
if (empty($favicon['path']) || !is_file($favicon['path'])) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Favicon not found';
    exit;
}

$mtime = (int) @filemtime($favicon['path']);
$etag = '"' . md5($favicon['path'] . '|' . $mtime . '|' . (int) @filesize($favicon['path'])) . '"';

header('Content-Type: ' . ($favicon['type'] !== '' ? $favicon['type'] : 'image/png'));
header('Cache-Control: public, max-age=86400, must-revalidate');
header('ETag: ' . $etag);
if ($mtime > 0) {
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
}

$ifNoneMatch = trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''));
if ($ifNoneMatch !== '' && trim($ifNoneMatch, '"') === trim($etag, '"')) {
    http_response_code(304);
    exit;
}

readfile($favicon['path']);
exit;
