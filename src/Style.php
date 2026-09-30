<?php

namespace GlpiPlugin\Glpistyle;

use GlpiPlugin\Glpistyle\Ui\Registry;

/**
 * Builds the CSS generated from the settings. Only values that went
 * through Config::sanitize() (hex colors, clamped ints, whitelisted enums)
 * and URLs built by the plugin itself are interpolated here.
 */
class Style
{
    private const BOX_WIDTHS = ['sm' => 380, 'md' => 440, 'lg' => 520];
    private const PANEL_WIDTHS = ['sm' => 440, 'md' => 540, 'lg' => 660];

    /** [horizontal, vertical] alignment of the box for each grid position */
    private const GRID = [
        'tl' => ['flex-start', 'flex-start'], 'tc' => ['center', 'flex-start'], 'tr' => ['flex-end', 'flex-start'],
        'ml' => ['flex-start', 'center'],     'mc' => ['center', 'center'],     'mr' => ['flex-end', 'center'],
        'bl' => ['flex-start', 'flex-end'],   'bc' => ['center', 'flex-end'],   'br' => ['flex-end', 'flex-end'],
    ];

    private const HERO_WIDTHS = ['sm' => 440, 'md' => 580, 'lg' => 760];

    private const TEXT_ALIGN_OF = ['flex-start' => 'left', 'center' => 'center', 'flex-end' => 'right'];
    private const FLEX_OF = ['left' => 'flex-start', 'center' => 'center', 'right' => 'flex-end'];

    private const BG_FITS = [
        'cover'   => ['cover', 'no-repeat'],
        'contain' => ['contain', 'no-repeat'],
        'center'  => ['auto', 'no-repeat'],
        'repeat'  => ['auto', 'repeat'],
    ];

    /**
     * Custom properties consumed by public/css/login.css.
     */
    public static function loginCss(array $config): string
    {
        $font = Config::FONTS[$config['login_font']] ?? Config::FONTS['inter'];
        $has_bg = Config::getAssetPath('login_bg', $config) !== null;
        [$h, $v] = self::GRID[$config['login_position']] ?? ['center', 'center'];
        [$size, $repeat] = self::BG_FITS[$config['login_bg_fit']];
        [$hero_h, $hero_v] = self::GRID[$config['hero_position']] ?? ['flex-start', 'center'];
        $hero_align = $config['hero_align'] === 'auto' ? self::TEXT_ALIGN_OF[$hero_h] : $config['hero_align'];
        // Glass behind the hero texts: dark tint under light text, light under dark
        $hero_backdrop = self::contrast($config['hero_text_color']) === '#ffffff'
            ? 'rgba(255,255,255,.62)'
            : 'rgba(10,14,30,.32)';

        $vars = [
            '--gs-font'        => $font[2],
            '--gs-accent'      => $config['login_accent'],
            '--gs-accent-fg'   => self::contrast($config['login_accent']),
            '--gs-radius'      => $config['login_radius'] . 'px',
            '--gs-logo-w'      => $config['login_logo_width'] . 'px',
            '--gs-logo-h'      => $config['login_logo_height'] . 'px',
            '--gs-box-w'       => self::BOX_WIDTHS[$config['login_box_width']] . 'px',
            '--gs-panel-w'     => self::PANEL_WIDTHS[$config['login_box_width']] . 'px',
            '--gs-box-alpha'   => $config['login_box_opacity'] . '%',
            '--gs-box-blur'    => $config['login_box_blur'] . 'px',
            '--gs-h'           => $h,
            '--gs-v'           => $v,
            '--gs-c1'          => $config['login_bg_color1'],
            '--gs-c2'          => $config['login_bg_color2'],
            '--gs-c3'          => $config['login_bg_color3'],
            '--gs-angle'       => $config['login_bg_angle'] . 'deg',
            '--gs-hero-text'   => $config['hero_text_color'],
            '--gs-hero-h'      => $hero_h,
            '--gs-hero-v'      => $hero_v,
            '--gs-hero-align'  => $hero_align,
            '--gs-hero-items'  => self::FLEX_OF[$hero_align],
            '--gs-hero-w'      => self::HERO_WIDTHS[$config['hero_width']] . 'px',
            '--gs-hero-backdrop' => $hero_backdrop,
            // Without a photo the gradient is the background itself
            '--gs-overlay'     => $has_bg ? (string) ($config['login_overlay_opacity'] / 100) : '1',
            '--gs-image'       => $has_bg ? 'url("' . Config::getAssetUrl('login_bg', $config) . '")' : 'none',
            '--gs-image-size'  => $size,
            '--gs-image-repeat' => $repeat,
            '--gs-image-pos'   => $config['login_bg_position'],
        ];

        $css = '';
        if ($font[1] !== null) {
            $css .= '@import url("https://fonts.googleapis.com/css2?family=' . $font[1] . '&display=swap");' . "\n";
        }
        $css .= 'body.gs-login{';
        foreach ($vars as $name => $value) {
            $css .= $name . ':' . $value . ';';
        }
        $css .= '}';

        // "Usar padrão do tema" colors are simply not overridden; the
        // tripled class outranks the light/dark theme tokens of login.css
        $overrides = [
            '--gs-box-bg'      => $config['login_box_bg'],
            '--gs-title-color' => $config['login_title_color'],
            '--gs-label-color' => $config['login_text_color'],
        ];
        $overrides = array_filter($overrides, static fn($value) => $value !== '');
        if ($overrides !== []) {
            $css .= 'body.gs-login.gs-login.gs-login{';
            foreach ($overrides as $name => $value) {
                $css .= $name . ':' . $value . ';';
            }
            $css .= '}';
        }

        $logo = self::loginLogoUrl($config);
        if ($logo !== '') {
            // Must outrank the light/dark default logo rules of login.css
            $css .= 'html body.gs-login.gs-login .page-anonymous .glpi-logo.glpi-logo{--logo:url("' . $logo . '") !important;}';
        }

        return $css;
    }

    public static function loginLogoUrl(array $config): string
    {
        foreach (['login_logo', 'logo_full'] as $slot) {
            if (Config::getAssetPath($slot, $config) !== null) {
                return Config::getAssetUrl($slot, $config);
            }
        }
        return '';
    }

    /**
     * Served by front/style.css.php on every page: inner pages colors and
     * menu logos, and the logo of the other anonymous pages (lost
     * password...).
     */
    public static function globalCss(array $config): string
    {
        // @import must precede every other rule of the stylesheet
        $imports = [];
        $features_css = '';
        foreach (Registry::enabled($config) as $feature) {
            $imports = array_merge($imports, $feature::imports($config));
            $features_css .= $feature::css($config);
        }

        $css = '';
        foreach (array_unique($imports) as $url) {
            $css .= '@import url("' . $url . '");' . "\n";
        }
        $css .= self::colorsCss($config) . $features_css;

        if (Config::getAssetPath('logo_full', $config) !== null) {
            $url = Config::getAssetUrl('logo_full', $config);
            $css .= '.page .glpi-logo{background-image:url("' . $url . '") !important;'
                . 'background-size:contain !important;background-position:center !important;background-repeat:no-repeat !important;'
                . 'width:' . $config['logo_full_width'] . 'px !important;height:' . $config['logo_full_height'] . 'px !important;}';
        }

        // Collapsed sidebar: dedicated square logo, or the full one shrunk
        $reduced_slot = Config::getAssetPath('logo_reduced', $config) !== null ? 'logo_reduced'
            : (Config::getAssetPath('logo_full', $config) !== null ? 'logo_full' : null);
        if ($reduced_slot !== null) {
            $url = Config::getAssetUrl($reduced_slot, $config);
            $css .= 'body.navbar-collapsed .navbar-brand .glpi-logo{background-image:url("' . $url . '") !important;'
                . 'background-size:contain !important;background-position:center !important;background-repeat:no-repeat !important;'
                . 'width:40px !important;height:40px !important;}';
        }

        $login_logo = self::loginLogoUrl($config);
        if ($login_logo !== '') {
            $css .= '.page-anonymous .glpi-logo{--logo:url("' . $login_logo . '") !important;'
                . 'width:auto !important;max-width:' . $config['login_logo_width'] . 'px;'
                . 'height:' . $config['login_logo_height'] . 'px !important;object-fit:contain;}';
        }

        return $css;
    }

    /**
     * Inner pages colors, layered over whatever GLPI palette the user
     * picked. Only the colors not left on "Usar padrão do tema" are set.
     */
    public static function colorsCss(array $config): string
    {
        $root = [];
        if ($config['color_primary'] !== '') {
            $root['--tblr-primary-rgb'] = self::rgb($config['color_primary']);
            $root['--tblr-primary'] = $config['color_primary'];
            $root['--tblr-primary-fg'] = self::contrast($config['color_primary']);
        }
        if ($config['color_secondary'] !== '') {
            $root['--tblr-secondary-rgb'] = self::rgb($config['color_secondary']);
            $root['--tblr-secondary'] = $config['color_secondary'];
        }
        if ($config['color_links'] !== '') {
            $root['--tblr-link-color-rgb'] = self::rgb($config['color_links']);
            $root['--tblr-link-color'] = $config['color_links'];
            $root['--tblr-link-hover-color'] = $config['color_links'];
        }
        if ($config['color_menu_bg'] !== '') {
            $root['--glpi-mainmenu-bg'] = $config['color_menu_bg'];
            // The stock GLPI logo is white: switch to its dark variant on a
            // light menu (an uploaded logo overrides the image anyway)
            if (self::contrast($config['color_menu_bg']) !== '#ffffff') {
                $root['--glpi-logo'] = 'var(--glpi-logo-dark)';
                $root['--glpi-logo-reduced'] = 'var(--glpi-logo-dark-reduced)';
            }
        }
        if ($config['color_menu_fg'] !== '') {
            $root['--glpi-mainmenu-fg'] = $config['color_menu_fg'];
        }

        $css = '';
        if ($root !== []) {
            // Same specificity as the core palettes plus one, so these win
            // over :root[data-glpi-theme="..."] whatever the load order
            $css .= ':root:root[data-glpi-theme]{';
            foreach ($root as $name => $value) {
                $css .= $name . ':' . $value . ';';
            }
            $css .= '}';
        }

        // Vertical menu layout only: with the horizontal menu the header *is*
        // the menu bar (.topbar) and already follows the menu colors
        $header = 'header.navbar[data-testid="main-header"]:not(.topbar)';
        if ($config['color_header_bg'] !== '') {
            $css .= $header . '{background-color:' . $config['color_header_bg'] . ' !important;}';
        }
        if ($config['color_header_fg'] !== '') {
            $fg = $config['color_header_fg'];
            $css .= $header . '{--tblr-body-color:' . $fg . ';--tblr-navbar-color:' . $fg . ';color:' . $fg . ' !important;}'
                . $header . ' .nav-link,' . $header . ' .btn-icon,' . $header . ' .breadcrumb a,' . $header . ' .breadcrumb-item,'
                . $header . ' .breadcrumb-item::before,' . $header . ' .ti,' . $header . ' .user-menu *{color:' . $fg . ' !important;}';
        }

        return $css;
    }

    private static function rgb(string $hex): string
    {
        return implode(', ', array_map('hexdec', str_split(substr($hex, 1), 2)));
    }

    /** Readable text color (#fff or near black) over the $hex background */
    public static function contrast(string $hex): string
    {
        [$r, $g, $b] = array_map(static function ($c) {
            $c = hexdec($c) / 255;
            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, str_split(substr($hex, 1), 2));
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        return $luminance > 0.4 ? '#0f172a' : '#ffffff';
    }
}
