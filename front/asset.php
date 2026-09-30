<?php

use GlpiPlugin\Glpistyle\Config;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

// Public (see Firewall strategy in setup.php): the login page needs the
// logo/background before anyone is authenticated. Only the fixed slots
// in Config::ASSETS can be requested - never an arbitrary file name.

$slot = (string) ($_GET['f'] ?? '');
$path = isset(Config::ASSETS[$slot]) ? Config::getAssetPath($slot) : null;

if ($path === null) {
    return new Response('', 404);
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$response = new BinaryFileResponse($path, 200, [
    'Content-Type'            => Config::MIME_TYPES[$ext] ?? 'application/octet-stream',
    'X-Content-Type-Options'  => 'nosniff',
    // Neutralizes anything active an SVG could still carry if opened directly
    'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
]);
// URLs carry the config revision (?r=), so they can be cached for good
$response->setPublic();
$response->setMaxAge(31536000);
$response->headers->addCacheControlDirective('immutable');

return $response;
