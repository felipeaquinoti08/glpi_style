<?php

namespace GlpiPlugin\Glpistyle;

/**
 * DISPLAY_LOGIN hook output. The core prints it inside the login form's
 * side column; public/js/login.js (mount(), called inline right after
 * this markup, while the page is still parsing) moves the hero panel to
 * <body> and applies the layout classes before the first paint.
 *
 * The <style> is printed here rather than in front/style.css.php so the
 * live preview (front/preview.php) can render unsaved settings.
 */
class Login
{
    /** Set by front/preview.php: unsaved settings to render instead of the stored ones */
    public static ?array $preview = null;

    public static function display(): void
    {
        $config = self::$preview ?? Config::get();
        if (!(int) $config['login_enabled'] && self::$preview === null) {
            return;
        }

        $e = static fn(string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        echo '<style id="gs-login-vars">' . Style::loginCss($config) . '</style>';

        // Decorative effects belong to the gradient: over a plain photo
        // (gradient intensity 0) they would only get in the way.
        $has_bg = Config::getAssetPath('login_bg', $config) !== null;
        $effects = !$has_bg || $config['login_overlay_opacity'] > 0;

        echo '<div class="gs-hero" id="gs-hero">';
        echo '<div class="gs-hero__image"></div>';
        echo '<div class="gs-hero__gradient"></div>';
        if ($effects && $config['login_pattern'] !== 'none') {
            echo '<div class="gs-hero__pattern gs-pattern-' . $e($config['login_pattern']) . '"></div>';
        }
        if ($effects && (int) $config['login_shapes']) {
            echo '<div class="gs-hero__shapes" aria-hidden="true"><span></span><span></span><span></span></div>';
        }

        echo '<div class="gs-hero__inner"><div class="gs-hero__content">';
        if ($config['hero_badge'] !== '') {
            echo '<span class="gs-hero__badge"><span class="gs-hero__dot"></span>' . $e($config['hero_badge']) . '</span>';
        }
        if ($config['hero_title'] !== '') {
            echo '<p class="gs-hero__title">' . $e($config['hero_title']) . '</p>';
        }
        if ($config['hero_subtitle'] !== '') {
            echo '<p class="gs-hero__subtitle">' . $e($config['hero_subtitle']) . '</p>';
        }
        if ($config['hero_features'] !== '') {
            echo '<ul class="gs-hero__features">';
            foreach (explode("\n", $config['hero_features']) as $feature) {
                echo '<li><span class="gs-hero__check"><i class="ti ti-check"></i></span>' . $e($feature) . '</li>';
            }
            echo '</ul>';
        }
        echo '</div></div>';
        echo '</div>';

        $client = [
            'position'        => $config['login_position'],
            'formTheme'       => $config['login_form_theme'],
            'logoPosition'    => $config['login_logo_position'],
            'textAlign'       => $config['login_text_align'],
            'buttonStyle'     => $config['login_button_style'],
            'titleDivider'    => (bool) $config['login_title_divider'],
            'glass'           => $config['login_box_blur'] > 0,
            'heroInCard'      => (bool) $config['hero_in_card'],
            'heroBackdrop'    => $config['hero_backdrop'],
            'animated'        => (bool) $config['login_bg_animated'],
            'formTitle'       => $config['form_title'],
            'formSubtitle'    => $config['form_subtitle'],
            'buttonText'      => $config['button_text'],
            'extraSeparator'  => $config['extra_separator'],
            'passwordToggle'  => (bool) $config['login_password_toggle'],
            'footerMode'      => $config['footer_mode'],
            'footerText'      => $config['footer_text'],
            'preview'         => self::$preview !== null,
        ];
        echo '<script type="application/json" id="gs-login-config">'
            . json_encode($client, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)
            . '</script>';
        echo '<script>window.GlpiStyleLogin && window.GlpiStyleLogin.mount();</script>';
    }
}
