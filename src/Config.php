<?php

namespace GlpiPlugin\Glpistyle;

use Config as GlpiConfig;
use RuntimeException;

/**
 * Typed access to the plugin's settings, persisted as rows in the core
 * `glpi_configs` table (context `plugin:glpistyle`) - no dedicated table
 * needed for a flat list of settings.
 *
 * Every value goes through sanitize() before being stored *and* before
 * being used for a live preview: colors, numbers and enums end up inside
 * generated CSS, so only strictly validated values may ever reach it.
 */
class Config
{
    public const CONTEXT = 'plugin:glpistyle';

    public const LAYOUTS = [
        'split_left'  => 'Dividido - destaque à esquerda',
        'split_right' => 'Dividido - destaque à direita',
        'centered'    => 'Centralizado (vidro sobre o fundo)',
    ];

    /** key => [label, Google Fonts family (null = no download), CSS font stack] */
    public const FONTS = [
        'inter'        => ['Inter', 'Inter:wght@400;500;600;700;800', "'Inter', system-ui, sans-serif"],
        'plus_jakarta' => ['Plus Jakarta Sans', 'Plus+Jakarta+Sans:wght@400;500;600;700;800', "'Plus Jakarta Sans', system-ui, sans-serif"],
        'manrope'      => ['Manrope', 'Manrope:wght@400;500;600;700;800', "'Manrope', system-ui, sans-serif"],
        'poppins'      => ['Poppins', 'Poppins:wght@400;500;600;700;800', "'Poppins', system-ui, sans-serif"],
        'montserrat'   => ['Montserrat', 'Montserrat:wght@400;500;600;700;800', "'Montserrat', system-ui, sans-serif"],
        'system'       => ['Fonte do sistema (sem download)', null, "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif"],
    ];

    public const FORM_THEMES = [
        'light' => 'Claro',
        'dark'  => 'Escuro',
    ];

    public const PATTERNS = [
        'none' => 'Nenhum',
        'dots' => 'Pontos',
        'grid' => 'Grade',
    ];

    /** Uploadable images: slot => [label, allowed extensions] */
    public const ASSETS = [
        'login_logo'   => ['Logo da tela de login', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']],
        'login_bg'     => ['Imagem de fundo do login', ['png', 'jpg', 'jpeg', 'webp']],
        'logo_full'    => ['Logo do menu (expandido)', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']],
        'logo_reduced' => ['Logo do menu (recolhido)', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']],
        'favicon'      => ['Favicon', ['ico', 'png', 'svg']],
    ];

    public const MIME_TYPES = [
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
    ];

    public const MAX_UPLOAD_BYTES = 8 * 1024 * 1024;

    /**
     * field => [type, default, extra]
     *  - bool:  extra unused
     *  - color: extra unused (#rrggbb)
     *  - int:   extra = [min, max]
     *  - enum:  extra = allowed keys
     *  - text:  extra = max length
     *  - lines: extra = max number of lines (each max 120 chars)
     */
    private static function schema(): array
    {
        return [
            'login_enabled'         => ['bool', 1],
            'login_layout'          => ['enum', 'split_left', array_keys(self::LAYOUTS)],
            'login_font'            => ['enum', 'inter', array_keys(self::FONTS)],
            'login_form_theme'      => ['enum', 'light', array_keys(self::FORM_THEMES)],
            'login_accent'          => ['color', '#6d5dfc'],
            'login_radius'          => ['int', 14, [0, 28]],
            'login_logo_height'     => ['int', 64, [24, 160]],

            'login_bg_color1'       => ['color', '#0b1026'],
            'login_bg_color2'       => ['color', '#2b1b6b'],
            'login_bg_color3'       => ['color', '#6d5dfc'],
            'login_bg_angle'        => ['int', 135, [0, 360]],
            'login_bg_animated'     => ['bool', 1],
            'login_shapes'          => ['bool', 1],
            'login_pattern'         => ['enum', 'dots', array_keys(self::PATTERNS)],
            'login_overlay_opacity' => ['int', 70, [0, 100]],

            'hero_text_color'       => ['color', '#ffffff'],
            'hero_badge'            => ['text', 'Central de Serviços de TI', 60],
            'hero_title'            => ['text', 'Tudo o que você precisa, em um só lugar.', 120],
            'hero_subtitle'         => ['text', 'Abra chamados, acompanhe solicitações e encontre respostas rápidas na nossa base de conhecimento.', 300],
            'hero_features'         => ['lines', "Atendimento ágil e rastreável\nBase de conhecimento sempre à mão\nAcompanhamento em tempo real", 6],

            'form_title'            => ['text', 'Bem-vindo de volta', 80],
            'form_subtitle'         => ['text', 'Entre com suas credenciais para continuar.', 160],
            'button_text'           => ['text', 'Entrar', 40],
            'extra_separator'       => ['text', 'ou continue com', 40],
            'login_password_toggle' => ['bool', 1],
            'footer_text'           => ['text', '', 160],
            'login_hide_copyright'  => ['bool', 0],

            // Stored file names of the uploaded images (see ASSETS)
            'login_logo'            => ['asset', ''],
            'login_bg'              => ['asset', ''],
            'logo_full'             => ['asset', ''],
            'logo_reduced'          => ['asset', ''],
            'favicon'               => ['asset', ''],

            // Bumped on every save; appended to CSS/image URLs as cache buster
            'revision'              => ['int', 1, [0, PHP_INT_MAX]],
        ];
    }

    public static function defaults(): array
    {
        return array_map(static fn(array $def) => $def[1], self::schema());
    }

    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache === null) {
            $stored = GlpiConfig::getConfigurationValues(self::CONTEXT, array_keys(self::schema()));
            self::$cache = self::sanitize($stored, self::defaults());
        }
        return self::$cache;
    }

    /**
     * Validates every known field of $input, falling back to $base for
     * missing/invalid ones. Unknown keys are dropped.
     *
     * With $from_form, $input is a submitted form (or preview query):
     * unchecked checkboxes are absent and mean 0, and asset fields and
     * the revision are never taken from it - only storeUpload() and
     * removeAsset() may change those.
     */
    public static function sanitize(array $input, array $base, bool $from_form = false): array
    {
        $clean = [];
        foreach (self::schema() as $field => $def) {
            [$type, $default] = $def;
            $extra = $def[2] ?? null;
            $current = $base[$field] ?? $default;

            if ($type === 'asset' || $field === 'revision') {
                $value = $from_form ? $current : ($input[$field] ?? $default);
                $clean[$field] = $type === 'asset'
                    ? (preg_match('/^[a-z_]+\.[a-z]{2,4}$/', (string) $value) ? (string) $value : '')
                    : max(0, (int) $value);
                continue;
            }

            if (!array_key_exists($field, $input)) {
                $clean[$field] = $type === 'bool' && $from_form ? 0 : $current;
                continue;
            }

            $value = $input[$field];
            switch ($type) {
                case 'bool':
                    $clean[$field] = (int) (bool) $value;
                    break;
                case 'color':
                    $value = strtolower(trim((string) $value));
                    $clean[$field] = preg_match('/^#[0-9a-f]{6}$/', $value) ? $value : $current;
                    break;
                case 'int':
                    $clean[$field] = is_numeric($value)
                        ? min($extra[1], max($extra[0], (int) $value))
                        : $current;
                    break;
                case 'enum':
                    $clean[$field] = in_array((string) $value, $extra, true) ? (string) $value : $current;
                    break;
                case 'text':
                    $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
                    $clean[$field] = mb_substr($value, 0, $extra);
                    break;
                case 'lines':
                    $lines = preg_split('/\R/u', (string) $value);
                    $lines = array_filter(array_map(
                        static fn($l) => mb_substr(trim(preg_replace('/\s+/u', ' ', $l)), 0, 120),
                        $lines
                    ), static fn($l) => $l !== '');
                    $clean[$field] = implode("\n", array_slice($lines, 0, $extra));
                    break;
            }
        }
        return $clean;
    }

    /**
     * Saves the editable (non-asset) fields from a submitted form.
     */
    public static function saveFromInput(array $input): void
    {
        $current = self::get();
        $values = self::sanitize($input, $current, true);
        $values['revision'] = $current['revision'] + 1;
        self::store($values);
    }

    private static function store(array $values): void
    {
        GlpiConfig::setConfigurationValues(self::CONTEXT, $values);
        self::$cache = null;
    }

    private static function bumpRevision(array $extra = []): void
    {
        $values = $extra;
        $values['revision'] = self::get()['revision'] + 1;
        self::store($values);
    }

    public static function getStorageDir(): string
    {
        return GLPI_PLUGIN_DOC_DIR . '/glpistyle';
    }

    public static function ensureStorageDir(): void
    {
        $dir = self::getStorageDir();
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Não foi possível criar o diretório %s', $dir));
        }
    }

    /**
     * Validates and stores one uploaded image ($_FILES entry) for $slot.
     *
     * @return string|null error message, null on success
     */
    public static function storeUpload(string $slot, array $file): ?string
    {
        if (!isset(self::ASSETS[$slot])) {
            return 'Campo de imagem desconhecido.';
        }
        [$label, $allowed] = self::ASSETS[$slot];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return sprintf('%s: falha no envio do arquivo.', $label);
        }
        if ($file['size'] > self::MAX_UPLOAD_BYTES) {
            return sprintf('%s: o arquivo excede %d MB.', $label, self::MAX_UPLOAD_BYTES / 1024 / 1024);
        }

        $ext = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            return sprintf('%s: formato não permitido (use %s).', $label, implode(', ', $allowed));
        }

        // Check the actual content too, not only the extension
        $detected = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $ok = match ($ext) {
            'svg'   => in_array($detected, ['image/svg+xml', 'image/svg', 'text/xml', 'application/xml', 'text/plain'], true)
                && self::isSafeSvg((string) file_get_contents($file['tmp_name'])),
            'ico'   => in_array($detected, ['image/x-icon', 'image/vnd.microsoft.icon', 'application/octet-stream'], true),
            default => $detected === self::MIME_TYPES[$ext],
        };
        if (!$ok) {
            return sprintf('%s: o conteúdo do arquivo não é uma imagem %s válida.', $label, strtoupper($ext));
        }

        self::ensureStorageDir();
        $name = $slot . '.' . $ext;
        $previous = self::get()[$slot];
        if (!move_uploaded_file($file['tmp_name'], self::getStorageDir() . '/' . $name)) {
            return sprintf('%s: não foi possível gravar o arquivo.', $label);
        }
        if ($previous !== '' && $previous !== $name) {
            @unlink(self::getStorageDir() . '/' . $previous);
        }

        self::bumpRevision([$slot => $name]);
        return null;
    }

    /**
     * Rejects SVGs carrying scripts/handlers/external references - they are
     * served from the GLPI origin, so an active SVG would be stored XSS.
     */
    private static function isSafeSvg(string $content): bool
    {
        if (!str_contains($content, '<svg')) {
            return false;
        }
        return preg_match('/<script|<foreignObject|\son[a-z]+\s*=|javascript:|<!ENTITY|xlink:href\s*=\s*["\'](?!#)|href\s*=\s*["\'](?!#)/i', $content) !== 1;
    }

    public static function removeAsset(string $slot): void
    {
        if (!isset(self::ASSETS[$slot])) {
            return;
        }
        $current = self::get()[$slot];
        if ($current !== '') {
            @unlink(self::getStorageDir() . '/' . $current);
        }
        self::bumpRevision([$slot => '']);
    }

    public static function getAssetPath(string $slot, ?array $config = null): ?string
    {
        $config ??= self::get();
        $name = $config[$slot] ?? '';
        if ($name === '') {
            return null;
        }
        $path = self::getStorageDir() . '/' . $name;
        return is_file($path) ? $path : null;
    }

    public static function getAssetUrl(string $slot, ?array $config = null): string
    {
        global $CFG_GLPI;

        $config ??= self::get();
        if (($config[$slot] ?? '') === '') {
            return '';
        }
        return $CFG_GLPI['root_doc'] . '/plugins/glpistyle/front/asset.php?f=' . rawurlencode($slot) . '&r=' . $config['revision'];
    }
}
