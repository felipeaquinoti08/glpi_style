<?php

namespace GlpiPlugin\Glpistyle\Ui;

/**
 * One optional improvement of the logged-in interface ("visual interno").
 *
 * Each improvement lives in its own src/Ui/<Name>Feature.php (settings,
 * editor section, generated CSS) plus public/css/ui/<key>.css (static
 * rules), and is discovered by Registry - adding one never touches the
 * shared files. Every improvement is off by default, so an untouched
 * install keeps the stock GLPI look.
 *
 * Static CSS only consumes custom properties; everything configurable is
 * emitted by css() through front/style.css.php, from values validated by
 * Config::sanitize() (see schema()).
 */
abstract class Feature
{
    /** Short identifier: settings prefix `ui_<key>_`, static file css/ui/<key>.css */
    abstract public static function key(): string;

    /** Position in the editor, lower first */
    abstract public static function order(): int;

    abstract public static function title(): string;

    abstract public static function subtitle(): string;

    /** Tabler icon class of the section badge */
    abstract public static function icon(): string;

    /** Section badge color: orange | purple | teal | blue | green | pink */
    public static function tone(): string
    {
        return 'blue';
    }

    /**
     * Settings of the improvement, same format as Config::schema(). The
     * `ui_<key>_enabled` switch is added automatically.
     */
    abstract protected static function fields(): array;

    /**
     * Editor section body.
     *
     * @param array<string, \Closure> $ui    renderers of front/config.php (card, switch, select...)
     * @param array                  $config current settings
     */
    abstract public static function section(array $ui, array $config): string;

    /** Generated CSS (custom properties...), only called while enabled */
    public static function css(array $config): string
    {
        return '';
    }

    /** Stylesheets that must be @import-ed first (e.g. web fonts), only while enabled */
    public static function imports(array $config): array
    {
        return [];
    }

    final public static function enabledField(): string
    {
        return 'ui_' . static::key() . '_enabled';
    }

    final public static function schema(): array
    {
        return [static::enabledField() => ['bool', 0]] + static::fields();
    }

    final public static function isEnabled(array $config): bool
    {
        return (int) ($config[static::enabledField()] ?? 0) === 1;
    }

    final public static function cssFile(): string
    {
        return 'css/ui/' . static::key() . '.css';
    }

    final public static function hasCssFile(): bool
    {
        return is_file(dirname(__DIR__, 2) . '/public/' . static::cssFile());
    }
}
