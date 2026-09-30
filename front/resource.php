<?php

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

// Serves the plugin's own CSS/JS through PHP. Static files under
// plugins/*/public are normally routed by GLPI itself, but web servers set
// up to answer *.css/*.js straight from glpi/public (try_files ... =404)
// never hand those requests to GLPI - this works with any setup.
// Public (see Firewall strategy in setup.php): used by the login page.

const GLPISTYLE_RESOURCES = [
    'login.css'   => ['css/login.css', 'text/css; charset=UTF-8'],
    'config.css'  => ['css/config.css', 'text/css; charset=UTF-8'],
    'login.js'    => ['js/login.js', 'text/javascript; charset=UTF-8'],
    'favicon.js'  => ['js/favicon.js', 'text/javascript; charset=UTF-8'],
    'config.js'   => ['js/config.js', 'text/javascript; charset=UTF-8'],
];

$key = (string) ($_GET['f'] ?? '');
if (!isset(GLPISTYLE_RESOURCES[$key])) {
    return new Response('', 404);
}
[$file, $type] = GLPISTYLE_RESOURCES[$key];

$response = new BinaryFileResponse(dirname(__DIR__) . '/public/' . $file, 200, [
    'Content-Type'           => $type,
    'X-Content-Type-Options' => 'nosniff',
]);
// URLs carry the plugin version (?v=)
$response->setPublic();
$response->setMaxAge(31536000);

return $response;
