<?php

use GlpiPlugin\Glpistyle\Config;
use GlpiPlugin\Glpistyle\Style;
use GlpiPlugin\Glpistyle\Ui\Registry;
use Symfony\Component\HttpFoundation\Response;

// Live preview of the logged-in interface on the config page itself (it
// is an internal page too): everything front/style.css.php and the
// enabled improvements' static files would produce, computed from the
// unsaved values in the query string. config.js swaps it in and disables
// the plugin's regular stylesheets while editing.

Session::checkRight('config', UPDATE);

$config = Config::sanitize($_GET, Config::get(), true);

$static = '';
foreach (Registry::enabled($config) as $feature) {
    if ($feature::hasCssFile()) {
        $static .= "\n" . file_get_contents(dirname(__DIR__) . '/public/' . $feature::cssFile());
    }
}

$response = new Response(Style::globalCss($config) . $static, 200, [
    'Content-Type'  => 'text/css; charset=UTF-8',
    'Cache-Control' => 'no-store',
]);

return $response;
