<?php

namespace GlpiPlugin\Glpistyle;

/**
 * Builds the CSS generated from the settings. Only values that went
 * through Config::sanitize() (hex colors, clamped ints, whitelisted enums)
 * and URLs built by the plugin itself are interpolated here.
 */
class Style
{
    /**
     * Custom properties consumed by public/css/login.css.
     */
    public static function loginCss(array $config): string
    {
        $font = Config::FONTS[$config['login_font']] ?? Config::FONTS['inter'];
        $bg_url = Config::getAssetUrl('login_bg', $config);
        $has_bg = $bg_url !== '' && Config::getAssetPath('login_bg', $config) !== null;

        $vars = [
            '--gs-font'          => $font[2],
            '--gs-accent'        => $config['login_accent'],
            '--gs-radius'        => $config['login_radius'] . 'px',
            '--gs-logo-h'        => $config['login_logo_height'] . 'px',
            '--gs-c1'            => $config['login_bg_color1'],
            '--gs-c2'            => $config['login_bg_color2'],
            '--gs-c3'            => $config['login_bg_color3'],
            '--gs-angle'         => $config['login_bg_angle'] . 'deg',
            '--gs-hero-text'     => $config['hero_text_color'],
            // Without a photo the gradient is the background itself
            '--gs-overlay'       => $has_bg ? (string) ($config['login_overlay_opacity'] / 100) : '1',
            '--gs-image'         => $has_bg ? 'url("' . $bg_url . '")' : 'none',
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
     * Served by front/style.css.php on every page: menu logos (logged
     * pages) and the logo of the other anonymous pages (lost password...).
     */
    public static function globalCss(array $config): string
    {
        $css = '';

        if (Config::getAssetPath('logo_full', $config) !== null) {
            $url = Config::getAssetUrl('logo_full', $config);
            $css .= '.page .glpi-logo{background-image:url("' . $url . '") !important;'
                . 'background-size:contain !important;background-position:center !important;background-repeat:no-repeat !important;}';
        }

        // Collapsed sidebar: dedicated square logo, or the full one shrunk
        $reduced_slot = Config::getAssetPath('logo_reduced', $config) !== null ? 'logo_reduced'
            : (Config::getAssetPath('logo_full', $config) !== null ? 'logo_full' : null);
        if ($reduced_slot !== null) {
            $url = Config::getAssetUrl($reduced_slot, $config);
            $css .= 'body.navbar-collapsed .navbar-brand .glpi-logo{background-image:url("' . $url . '") !important;'
                . 'background-size:contain !important;background-position:center !important;background-repeat:no-repeat !important;}';
        }

        $login_logo = self::loginLogoUrl($config);
        if ($login_logo !== '') {
            $css .= '.page-anonymous .glpi-logo{--logo:url("' . $login_logo . '") !important;'
                . 'width:auto !important;max-width:260px;height:' . $config['login_logo_height'] . 'px !important;object-fit:contain;}';
        }

        return $css;
    }
}
