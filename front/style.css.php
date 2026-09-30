<?php

use GlpiPlugin\Glpistyle\Config;
use GlpiPlugin\Glpistyle\Style;
use Symfony\Component\HttpFoundation\Response;

// Public (see Firewall strategy in setup.php): also loaded by the
// anonymous pages. URL is versioned by the config revision.

$response = new Response(Style::globalCss(Config::get()), 200, [
    'Content-Type' => 'text/css; charset=UTF-8',
]);
$response->setPublic();
$response->setMaxAge(31536000);

return $response;
