<?php

use Glpi\Http\Firewall;
use Glpi\Plugin\Hooks;
use GlpiPlugin\Glpistyle\Config;
use GlpiPlugin\Glpistyle\Login;

define('PLUGIN_GLPISTYLE_VERSION', '1.0.0');
define('PLUGIN_GLPISTYLE_MIN_GLPI_VERSION', '11.0.0');
define('PLUGIN_GLPISTYLE_MAX_GLPI_VERSION', '11.9.99');

function plugin_version_glpistyle(): array
{
    return [
        'name'         => 'GLPI Style',
        'version'      => PLUGIN_GLPISTYLE_VERSION,
        'author'       => 'Felipe Aquino',
        'license'      => 'GPL-3.0-or-later',
        'homepage'     => 'https://github.com/felipeaquinoti08/glpi_style',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_GLPISTYLE_MIN_GLPI_VERSION,
                'max' => PLUGIN_GLPISTYLE_MAX_GLPI_VERSION,
            ],
        ],
    ];
}

/**
 * URL of a file served by front/resource.php, versioned by its mtime so
 * browsers pick up changes without bumping the plugin version (which would
 * make GLPI disable the plugin until it's updated).
 */
function plugin_glpistyle_resource(string $key, string $file): string
{
    return 'front/resource.php?f=' . $key . '&m=' . (@filemtime(__DIR__ . '/public/' . $file) ?: 0);
}

function plugin_init_glpistyle(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT]['glpistyle'] = true;

    // Adds the gear/"Configure" icon next to the plugin in Setup > Plugins.
    $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['glpistyle'] = 'front/config.php';

    // asset.php serves the uploaded logos/background and style.css.php the
    // generated logo/favicon overrides - both are requested by the login
    // page itself, before anyone is authenticated.
    Firewall::addPluginStrategyForLegacyScripts('glpistyle', '#^/front/asset\.php#', Firewall::STRATEGY_NO_CHECK);
    Firewall::addPluginStrategyForLegacyScripts('glpistyle', '#^/front/style\.css\.php#', Firewall::STRATEGY_NO_CHECK);
    Firewall::addPluginStrategyForLegacyScripts('glpistyle', '#^/front/resource\.php#', Firewall::STRATEGY_NO_CHECK);

    if (!Plugin::isPluginActive('glpistyle')) {
        return;
    }

    $config = Config::get();

    // Logo/favicon overrides, on every page (logged or not). The revision
    // in the query string busts browser caches right after each save.
    $style_css = 'front/style.css.php?r=' . $config['revision'];
    $PLUGIN_HOOKS[Hooks::ADD_CSS]['glpistyle'] = [$style_css];
    $PLUGIN_HOOKS[Hooks::ADD_CSS_ANONYMOUS_PAGE]['glpistyle'] = [$style_css];

    if ($config['favicon'] !== '') {
        // The core <link rel="shortcut icon"> is printed after plugin header
        // tags and wins, so favicon.js swaps its href using this meta.
        $favicon_tags = [[
            'tag'        => 'meta',
            'properties' => ['name' => 'glpistyle:favicon', 'content' => Config::getAssetUrl('favicon', $config)],
        ]];
        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG]['glpistyle'] = $favicon_tags;
        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG_ANONYMOUS_PAGE]['glpistyle'] = $favicon_tags;
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['glpistyle'][] = plugin_glpistyle_resource('favicon.js', 'js/favicon.js');
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE]['glpistyle'][] = plugin_glpistyle_resource('favicon.js', 'js/favicon.js');
    }

    if ((int) $config['login_enabled'] === 1) {
        $PLUGIN_HOOKS[Hooks::ADD_CSS_ANONYMOUS_PAGE]['glpistyle'][] = plugin_glpistyle_resource('login.css', 'css/login.css');
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE]['glpistyle'][] = plugin_glpistyle_resource('login.js', 'js/login.js');

        // Only fired by templates/pages/login.html.twig - this is what
        // scopes the redesign to the login page and not to the other
        // anonymous pages (lost password, etc.).
        $PLUGIN_HOOKS[Hooks::DISPLAY_LOGIN]['glpistyle'] = [Login::class, 'display'];
    }
}

function plugin_glpistyle_install(): bool
{
    include_once(__DIR__ . '/install/install.php');
    return plugin_glpistyle_install_run();
}

function plugin_glpistyle_uninstall(): bool
{
    include_once(__DIR__ . '/install/uninstall.php');
    return plugin_glpistyle_uninstall_run();
}
