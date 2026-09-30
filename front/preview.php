<?php

use Glpi\Application\View\TemplateRenderer;
use Glpi\Plugin\Hooks;
use GlpiPlugin\Glpistyle\Config;
use GlpiPlugin\Glpistyle\Login;

// Live preview shown in the config page iframe: the real core login
// template, rendered with the settings being edited (query string,
// not saved yet). The form inside is inert - see login.js preview mode.

Session::checkRight('config', UPDATE);

global $CFG_GLPI, $PLUGIN_HOOKS;

$config = isset($_GET['_live']) ? Config::sanitize($_GET, Config::get(), true) : Config::get();
Login::$preview = $config;

// Preview even while the redesign is switched off on the real login page
$PLUGIN_HOOKS[Hooks::DISPLAY_LOGIN]['glpistyle'] = [Login::class, 'display'];
foreach ([
    Hooks::ADD_CSS_ANONYMOUS_PAGE        => plugin_glpistyle_resource('login.css', 'css/login.css'),
    Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE => plugin_glpistyle_resource('login.js', 'js/login.js'),
] as $hook => $file) {
    if (!in_array($file, (array) ($PLUGIN_HOOKS[$hook]['glpistyle'] ?? []), true)) {
        $PLUGIN_HOOKS[$hook]['glpistyle'][] = $file;
    }
}

TemplateRenderer::getInstance()->display('pages/login.html.twig', [
    'rand'                => mt_rand(),
    'card_bg_width'       => true,
    'lang'                => $CFG_GLPI['languages'][$_SESSION['glpilanguage']][3] ?? 'pt-BR',
    'title'               => __('Authentication'),
    'noAuto'              => 1,
    'redirect'            => '',
    'text_login'          => $CFG_GLPI['text_login'],
    'show_lost_password'  => true,
    'right_panel'         => true,
    'auth_dropdown_login' => Auth::dropdownLogin(false),
    'copyright_message'   => Html::getCopyrightMessage(false),
    'must_call_cron'      => false,
]);
