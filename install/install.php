<?php

use GlpiPlugin\Glpistyle\Config;

function plugin_glpistyle_install_run(): bool
{
    // Only fills in keys that don't exist yet, so reinstalling/upgrading
    // never overwrites what was already customized.
    $stored = \Config::getConfigurationValues(Config::CONTEXT);
    $missing = array_diff_key(Config::defaults(), $stored);
    if ($missing !== []) {
        \Config::setConfigurationValues(Config::CONTEXT, $missing);
    }

    Config::ensureStorageDir();

    return true;
}
