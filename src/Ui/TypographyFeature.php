<?php

namespace GlpiPlugin\Glpistyle\Ui;

use GlpiPlugin\Glpistyle\Config;

/**
 * Font and density of the whole logged-in interface.
 */
class TypographyFeature extends Feature
{
    public const DENSITIES = [
        'compact'     => 'Compacto',
        'normal'      => 'Normal (padrão do GLPI)',
        'comfortable' => 'Confortável',
    ];

    public const HEADINGS = [
        'glpi'   => 'Padrão do GLPI',
        'strong' => 'Mais fortes e compactos',
    ];

    public static function key(): string
    {
        return 'typography';
    }

    public static function order(): int
    {
        return 40;
    }

    public static function title(): string
    {
        return 'Fonte e densidade';
    }

    public static function subtitle(): string
    {
        return 'Fonte, tamanho do texto e espaçamento de todo o GLPI';
    }

    public static function icon(): string
    {
        return 'ti-typography';
    }

    public static function tone(): string
    {
        return 'pink';
    }

    /** 'glpi' keeps the core font stack, the rest reuse the login fonts */
    public static function fonts(): array
    {
        $fonts = ['glpi' => 'Padrão do GLPI (sem download)'];
        foreach (Config::FONTS as $key => [$label]) {
            if ($key !== 'system') {
                $fonts[$key] = $label;
            }
        }
        return $fonts;
    }

    protected static function fields(): array
    {
        return [
            'ui_font'      => ['enum', 'glpi', array_keys(self::fonts())],
            'ui_font_size' => ['int', 14, [12, 17]],
            'ui_density'   => ['enum', 'normal', array_keys(self::DENSITIES)],
            'ui_headings'  => ['enum', 'glpi', array_keys(self::HEADINGS)],
        ];
    }

    public static function section(array $ui, array $config): string
    {
        return '<div class="gs-row gs-row--3">'
            . $ui['card']('ti-letter-case', 'Fonte', $ui['select']('ui_font', 'Família', self::fonts(), 'As fontes do Google são baixadas pelo navegador de cada usuário (fonts.googleapis.com).')
                . $ui['range']('ui_font_size', 'Tamanho base', 12, 17, 'px', 1, 'O padrão do GLPI é 14px. Tudo o que usa o tamanho base acompanha: textos, campos, menus.'))
            . $ui['card']('ti-arrows-vertical', 'Densidade', $ui['select']('ui_density', 'Espaçamento', self::DENSITIES, 'Margem do conteúdo, respiro dentro dos cards e distância entre os campos dos formulários.'))
            . $ui['card']('ti-heading', 'Títulos', $ui['select']('ui_headings', 'Estilo', self::HEADINGS))
            . '</div>';
    }

    public static function imports(array $config): array
    {
        $font = Config::FONTS[$config['ui_font']] ?? null;
        return $font !== null && $font[1] !== null
            ? ['https://fonts.googleapis.com/css2?family=' . $font[1] . '&display=swap']
            : [];
    }

    public static function css(array $config): string
    {
        $root = [];
        $font = Config::FONTS[$config['ui_font']] ?? null;
        if ($font !== null) {
            $root['--tblr-font-sans-serif'] = $font[2];
            $root['--tblr-body-font-family'] = $font[2];
        }
        if ($config['ui_font_size'] !== 14) {
            $root['--tblr-body-font-size'] = $config['ui_font_size'] . 'px';
        }
        if ($config['ui_density'] === 'compact') {
            $root['--glpi-content-margin'] = '16px';
            $root['--gs-card-body-padding'] = '.75rem 1rem';
            $root['--gs-field-gap'] = '.2rem';
        } elseif ($config['ui_density'] === 'comfortable') {
            $root['--glpi-content-margin'] = '32px';
            $root['--gs-card-body-padding'] = '1.5rem 1.75rem';
            $root['--gs-field-gap'] = '.8rem';
        }

        $css = '';
        if ($root !== []) {
            $css .= ':root:root[data-glpi-theme]{';
            foreach ($root as $name => $value) {
                $css .= $name . ':' . $value . ';';
            }
            $css .= '}';
        }

        if ($config['ui_density'] !== 'normal') {
            // Spacing utilities (.p-0, .mb-0...) keep precedence: they are !important
            $css .= '.page-body .card > .card-body{padding:var(--gs-card-body-padding);}'
                . '.page-body .form-field.mb-2{margin-bottom:var(--gs-field-gap) !important;}';
        }

        if ($config['ui_headings'] === 'strong') {
            $css .= '.page-body h1,.page-body h2,.page-body h3,.page-body h4,.page-body .card-title{font-weight:650;letter-spacing:-.015em;}'
                . '.page-body h1,.page-body h2{letter-spacing:-.025em;}';
        }

        return $css;
    }
}
