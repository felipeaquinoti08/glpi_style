<?php

use GlpiPlugin\Glpistyle\Config;

function plugin_glpistyle_uninstall_run(): bool
{
    global $DB;

    $DB->doQuery("DELETE FROM `glpi_configs` WHERE `context` = '" . Config::CONTEXT . "'");

    $dir = Config::getStorageDir();
    if (is_dir($dir)) {
        foreach (glob($dir . '/*') ?: [] as $file) {
            @unlink($file);
        }
        @rmdir($dir);
    }

    return true;
}
