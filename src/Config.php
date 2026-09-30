<?php

namespace GlpiPlugin\Glpistyle;

use Config as GlpiConfig;
use GlpiPlugin\Glpistyle\Ui\Registry;
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

    /** Where the login box sits: 3x3 grid over the background, or a full-height side panel */
    public const POSITIONS = [
        'tl' => 'Canto superior esquerdo',
        'tc' => 'Topo, centralizada',
        'tr' => 'Canto superior direito',
        'ml' => 'Meio, à esquerda',
        'mc' => 'Centro da tela',
        'mr' => 'Meio, à direita',
        'bl' => 'Canto inferior esquerdo',
        'bc' => 'Base, centralizada',
        'br' => 'Canto inferior direito',
        'panel_left'  => 'Painel lateral esquerdo (altura total)',
        'panel_right' => 'Painel lateral direito (altura total)',
    ];

    /** Grid positions only (no side panels): used by the hero content */
    public const GRID_POSITIONS = ['tl', 'tc', 'tr', 'ml', 'mc', 'mr', 'bl', 'bc', 'br'];

    public const HERO_ALIGNS = [
        'auto'   => 'Automático (segue a posição)',
        'left'   => 'À esquerda',
        'center' => 'Centralizado',
        'right'  => 'À direita',
    ];

    public const HERO_WIDTHS = [
        'sm' => 'Estreito',
        'md' => 'Médio',
        'lg' => 'Largo',
    ];

    public const HERO_BACKDROPS = [
        'none'  => 'Sem fundo (texto direto sobre a imagem)',
        'glass' => 'Cartão de vidro (melhora a leitura sobre fotos)',
    ];

    public const BOX_WIDTHS = [
        'sm' => 'Pequena',
        'md' => 'Média',
        'lg' => 'Grande',
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
        'none' => 'Nenhuma',
        'dots' => 'Pontos',
        'grid' => 'Grade',
    ];

    /** key => [label, help] */
    public const BG_FITS = [
        'cover'   => ['Cobrir a tela', 'Preenche a tela inteira, cortando o que passar das bordas.'],
        'contain' => ['Mostrar inteira', 'A imagem aparece completa; sobra espaço com a cor 1 do fundo.'],
        'center'  => ['Tamanho original', 'Sem redimensionar, no ponto escolhido em "Ponto de foco".'],
        'repeat'  => ['Repetir (mosaico)', 'Repete a imagem lado a lado. Bom para texturas.'],
    ];

    public const BG_POSITIONS = [
        'center' => 'Centro',
        'top'    => 'Topo',
        'bottom' => 'Base',
        'left'   => 'Esquerda',
        'right'  => 'Direita',
    ];

    public const LOGO_POSITIONS = [
        'inside' => 'Dentro da caixa',
        'above'  => 'Acima da caixa, sobre o fundo',
    ];

    public const TEXT_ALIGNS = [
        'center' => 'Centralizado',
        'left'   => 'À esquerda',
    ];

    public const BUTTON_STYLES = [
        'gradient' => 'Gradiente com brilho',
        'flat'     => 'Cor sólida',
    ];

    public const FOOTER_MODES = [
        'glpi'   => 'Copyright do GLPI',
        'custom' => 'Texto personalizado',
        'both'   => 'Texto personalizado + copyright do GLPI',
        'hidden' => 'Ocultar rodapé',
    ];

    /** Uploadable images: slot => [label, allowed extensions] */
    public const ASSETS = [
        'login_logo'   => ['Logo do login', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']],
        'login_bg'     => ['Imagem de fundo do login', ['png', 'jpg', 'jpeg', 'webp']],
        'logo_full'    => ['Logo do menu', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']],
        'logo_reduced' => ['Logo do menu recolhido', ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']],
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

    /** Suffix of the "Usar padrão do tema" checkbox of an optional color */
    public const DEFAULT_SUFFIX = '__default';

    /**
     * field => [type, default, extra]
     *  - bool:      extra unused
     *  - color:     #rrggbb
     *  - color_opt: #rrggbb, or '' meaning "use the theme default"
     *  - int:       extra = [min, max]
     *  - enum:      extra = allowed keys
     *  - text:      extra = max length
     *  - lines:     extra = max number of lines (each max 120 chars)
     */
    private static function schema(): array
    {
        return self::coreSchema() + Registry::schema();
    }

    private static function coreSchema(): array
    {
        return [
            // Login - general
            'login_enabled'         => ['bool', 1],
            'login_form_theme'      => ['enum', 'light', array_keys(self::FORM_THEMES)],
            'login_font'            => ['enum', 'inter', array_keys(self::FONTS)],
            'login_radius'          => ['int', 12, [0, 28]],

            // Login - box
            'login_position'        => ['enum', 'panel_right', array_keys(self::POSITIONS)],
            'login_box_width'       => ['enum', 'md', array_keys(self::BOX_WIDTHS)],
            'login_box_bg'          => ['color_opt', ''],
            'login_box_opacity'     => ['int', 100, [0, 100]],
            'login_box_blur'        => ['int', 0, [0, 40]],
            'login_title_color'     => ['color_opt', ''],
            'login_text_color'      => ['color_opt', ''],
            'login_accent'          => ['color', '#6d5dfc'],
            'login_button_style'    => ['enum', 'gradient', array_keys(self::BUTTON_STYLES)],
            'login_text_align'      => ['enum', 'center', array_keys(self::TEXT_ALIGNS)],
            'login_title_divider'   => ['bool', 0],
            'login_logo_position'   => ['enum', 'inside', array_keys(self::LOGO_POSITIONS)],
            'login_logo_width'      => ['int', 220, [40, 480]],
            'login_logo_height'     => ['int', 72, [20, 240]],

            // Login - background
            'login_bg_fit'          => ['enum', 'cover', array_keys(self::BG_FITS)],
            'login_bg_position'     => ['enum', 'center', array_keys(self::BG_POSITIONS)],
            'login_overlay_opacity' => ['int', 0, [0, 100]],
            'login_bg_color1'       => ['color', '#0b1026'],
            'login_bg_color2'       => ['color', '#2b1b6b'],
            'login_bg_color3'       => ['color', '#6d5dfc'],
            'login_bg_angle'        => ['int', 135, [0, 360]],
            'login_bg_animated'     => ['bool', 1],
            'login_shapes'          => ['bool', 1],
            'login_pattern'         => ['enum', 'dots', array_keys(self::PATTERNS)],

            // Login - hero texts (side panel layouts)
            'hero_text_color'       => ['color', '#ffffff'],
            'hero_position'         => ['enum', 'ml', self::GRID_POSITIONS],
            'hero_align'            => ['enum', 'auto', array_keys(self::HERO_ALIGNS)],
            'hero_width'            => ['enum', 'md', array_keys(self::HERO_WIDTHS)],
            'hero_backdrop'         => ['enum', 'none', array_keys(self::HERO_BACKDROPS)],
            'hero_in_card'          => ['bool', 0],
            'hero_badge'            => ['text', 'Central de Serviços de TI', 60],
            'hero_title'            => ['text', 'Tudo o que você precisa, em um só lugar.', 120],
            'hero_subtitle'         => ['text', 'Abra chamados, acompanhe solicitações e encontre respostas rápidas na nossa base de conhecimento.', 300],
            'hero_features'         => ['lines', "Atendimento ágil e rastreável\nBase de conhecimento sempre à mão\nAcompanhamento em tempo real", 6],

            // Login - box texts
            'form_title'            => ['text', 'Bem-vindo de volta', 80],
            'form_subtitle'         => ['text', 'Entre com suas credenciais para continuar.', 160],
            'button_text'           => ['text', 'Entrar', 40],
            'extra_separator'       => ['text', 'ou continue com', 40],
            'login_password_toggle' => ['bool', 1],
            'footer_mode'           => ['enum', 'glpi', array_keys(self::FOOTER_MODES)],
            'footer_text'           => ['text', '', 160],

            // Internal pages
            'logo_full_width'       => ['int', 100, [40, 240]],
            'logo_full_height'      => ['int', 55, [20, 120]],
            'color_primary'         => ['color_opt', ''],
            'color_secondary'       => ['color_opt', ''],
            'color_links'           => ['color_opt', ''],
            'color_menu_bg'         => ['color_opt', ''],
            'color_menu_fg'         => ['color_opt', ''],
            'color_header_bg'       => ['color_opt', ''],
            'color_header_fg'       => ['color_opt', ''],

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

    /** Names of the optional (theme default) color fields */
    public static function optionalColors(): array
    {
        return array_keys(array_filter(self::schema(), static fn($def) => $def[0] === 'color_opt'));
    }

    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache === null) {
            $stored = GlpiConfig::getConfigurationValues(self::CONTEXT);
            self::$cache = self::sanitize(self::migrate($stored), self::defaults());
        }
        return self::$cache;
    }

    /**
     * Maps settings saved by 1.0.0 (before the position picker) to the
     * current fields, so upgrading keeps the look that was configured.
     */
    private static function migrate(array $stored): array
    {
        if (!isset($stored['login_position']) && isset($stored['login_layout'])) {
            $stored['login_position'] = [
                'split_left'  => 'panel_right',
                'split_right' => 'panel_left',
                'centered'    => 'mc',
            ][$stored['login_layout']] ?? 'panel_right';
        }
        if (!isset($stored['footer_mode']) && isset($stored['login_hide_copyright'])) {
            $has_text = trim((string) ($stored['footer_text'] ?? '')) !== '';
            $stored['footer_mode'] = (int) $stored['login_hide_copyright']
                ? ($has_text ? 'custom' : 'hidden')
                : ($has_text ? 'both' : 'glpi');
        }
        if (!isset($stored['login_logo_width']) && isset($stored['login_logo_height'])) {
            $stored['login_logo_width'] = 240;
        }
        return $stored;
    }

    /**
     * Validates every known field of $input, falling back to $base for
     * missing/invalid ones. Unknown keys are dropped.
     *
     * With $from_form, $input is a submitted form (or preview query):
     * unchecked checkboxes are absent and mean 0, a checked "usar padrão
     * do tema" box empties its color, and asset fields and the revision
     * are never taken from it - only storeUpload() and removeAsset() may
     * change those.
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

            if ($type === 'color_opt' && $from_form && !empty($input[$field . self::DEFAULT_SUFFIX])) {
                $clean[$field] = '';
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
                case 'color_opt':
                    $value = strtolower(trim((string) $value));
                    $clean[$field] = preg_match('/^#[0-9a-f]{6}$/', $value) || ($type === 'color_opt' && $value === '')
                        ? $value
                        : $current;
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

    /**
     * Back to the default look; uploaded images are kept.
     */
    public static function reset(): void
    {
        $current = self::get();
        $values = self::sanitize(self::defaults(), $current);
        foreach (array_keys(self::ASSETS) as $slot) {
            $values[$slot] = $current[$slot];
        }
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
