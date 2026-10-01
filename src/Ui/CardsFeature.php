<?php

namespace GlpiPlugin\Glpistyle\Ui;

/**
 * Cards and forms: item forms, section headers, fields, buttons and tabs.
 */
class CardsFeature extends Feature
{
    public const SHADOWS = [
        'none'   => 'Sem sombra',
        'soft'   => 'Suave',
        'medium' => 'Média',
        'strong' => 'Forte',
    ];

    public const HEADERS = [
        'plain'  => 'Simples (padrão do GLPI)',
        'accent' => 'Título com barra de destaque',
        'tinted' => 'Fundo levemente colorido',
    ];

    public const FIELD_SIZES = [
        'compact' => 'Compacto',
        'normal'  => 'Normal',
        'large'   => 'Grande',
    ];

    public const TAB_STYLES = [
        'default' => 'Padrão do GLPI',
        'accent'  => 'Aba ativa na cor primária',
        'pills'   => 'Pílulas',
    ];

    private const SHADOW_VALUES = [
        'none'   => 'none',
        'soft'   => '0 1px 2px rgba(15,23,42,.04),0 1px 3px rgba(15,23,42,.06)',
        'medium' => '0 2px 4px rgba(15,23,42,.04),0 10px 24px -8px rgba(15,23,42,.14)',
        'strong' => '0 4px 8px rgba(15,23,42,.05),0 20px 44px -14px rgba(15,23,42,.26)',
    ];

    /** [min-height, vertical padding] of inputs */
    private const FIELD_METRICS = [
        'compact' => ['1.95rem', '.25rem'],
        'large'   => ['2.65rem', '.55rem'],
    ];

    public static function key(): string
    {
        return 'cards';
    }

    public static function order(): int
    {
        return 20;
    }

    public static function title(): string
    {
        return 'Cards e formulários';
    }

    public static function subtitle(): string
    {
        return 'Cantos, sombras, títulos de seção, campos, botões e abas';
    }

    public static function icon(): string
    {
        return 'ti-forms';
    }

    public static function tone(): string
    {
        return 'green';
    }

    protected static function fields(): array
    {
        return [
            'ui_cards_radius'   => ['int', 12, [0, 24]],
            'ui_cards_shadow'   => ['enum', 'soft', array_keys(self::SHADOWS)],
            'ui_cards_header'   => ['enum', 'accent', array_keys(self::HEADERS)],
            'ui_fields_size'    => ['enum', 'normal', array_keys(self::FIELD_SIZES)],
            'ui_fields_radius'  => ['int', 8, [0, 20]],
            'ui_buttons_radius' => ['int', 8, [0, 24]],
            'ui_tabs_style'     => ['enum', 'accent', array_keys(self::TAB_STYLES)],
            'ui_sticky_buttons' => ['bool', 1],
        ];
    }

    public static function section(array $ui, array $config): string
    {
        return '<div class="gs-row gs-row--3">'
            . $ui['card']('ti-square-rounded', 'Cards', $ui['range']('ui_cards_radius', 'Arredondamento', 0, 24, 'px')
                . $ui['select']('ui_cards_shadow', 'Sombra', self::SHADOWS)
                . $ui['select']('ui_cards_header', 'Títulos de seção', self::HEADERS), 'Blocos de formulário, painéis e a linha do tempo dos chamados.')
            . $ui['card']('ti-input-search', 'Campos', $ui['select']('ui_fields_size', 'Tamanho', self::FIELD_SIZES)
                . $ui['range']('ui_fields_radius', 'Arredondamento', 0, 20, 'px'), 'Campos de texto, listas de seleção e o foco na cor primária.')
            . $ui['card']('ti-click', 'Botões e abas', $ui['range']('ui_buttons_radius', 'Arredondamento dos botões', 0, 24, 'px')
                . $ui['select']('ui_tabs_style', 'Abas', self::TAB_STYLES)
                . $ui['switch']('ui_sticky_buttons', 'Botões de salvar fixos no rodapé', 'A barra com Salvar/Excluir fica presa no rodapé da janela, sempre visível, sem cobrir os campos.'))
            . '</div>';
    }

    public static function css(array $config): string
    {
        $css = ':root{'
            . '--gs-card-radius:' . $config['ui_cards_radius'] . 'px;'
            . '--gs-card-shadow:' . self::SHADOW_VALUES[$config['ui_cards_shadow']] . ';'
            . '--gs-field-radius:' . $config['ui_fields_radius'] . 'px;'
            . '--gs-btn-radius:' . $config['ui_buttons_radius'] . 'px;'
            . '}';

        // Section titles: `.card.border-0.shadow-none > .card-header.border-top` (fields_macros)
        $section_header = '.page-body .card.border-0.shadow-none > .card-header';
        if ($config['ui_cards_header'] === 'accent') {
            $css .= $section_header . '{border-top:0 !important;}'
                . $section_header . ' > .card-title{display:flex;align-items:center;gap:.55rem;font-weight:600;}'
                . $section_header . ' > .card-title::before{content:"";flex:none;width:4px;height:1.05em;border-radius:3px;background:var(--tblr-primary);}';
        } elseif ($config['ui_cards_header'] === 'tinted') {
            $css .= $section_header . ',.page-body .card-header.main-header{'
                . 'border-top:0 !important;border-radius:calc(var(--gs-card-radius) * .6);'
                . 'background:color-mix(in srgb,var(--tblr-primary) 6%,var(--tblr-bg-surface));}'
                . $section_header . ' > .card-title{font-weight:600;color:color-mix(in srgb,var(--tblr-primary) 70%,var(--tblr-body-color));}';
        }

        if (isset(self::FIELD_METRICS[$config['ui_fields_size']])) {
            [$height, $padding] = self::FIELD_METRICS[$config['ui_fields_size']];
            $css .= '.page-body .form-control:not(textarea):not(.form-control-sm):not(.form-control-lg),.page-body .form-select:not([multiple]):not(.form-select-sm){'
                . 'min-height:' . $height . ';padding-top:' . $padding . ';padding-bottom:' . $padding . ';}'
                . '.page-body .select2-container .select2-selection--single{height:' . $height . ' !important;display:flex;align-items:center;}'
                . '.page-body .select2-container .select2-selection--single .select2-selection__arrow{height:100% !important;}'
                . '.page-body .select2-container .select2-selection--multiple{min-height:' . $height . ' !important;}';
        }

        if ($config['ui_tabs_style'] === 'accent') {
            $css .= ':root:root[data-glpi-theme]{'
                . '--glpi-tabs-active-border-color:var(--tblr-primary);'
                . '--glpi-tabs-active-fg:color-mix(in srgb,var(--tblr-primary) 80%,var(--tblr-body-color));}';
        } elseif ($config['ui_tabs_style'] === 'pills') {
            $css .= self::PILLS_CSS;
        }

        if ((int) $config['ui_sticky_buttons']) {
            // Fixed to the bottom of the window (a sticky bar floated over
            // the fields of long forms). Only the item's main form, never
            // forms shown in modals; the page reserves the bar's height so
            // nothing ends up hidden under it.
            $bar = '.page-body form#main-form .form-button-separator';
            $css .= $bar . ':not(.modal *){position:fixed;left:0;right:0;bottom:0;z-index:1025;'
                . 'margin:0 !important;padding:10px 24px !important;min-height:60px;align-items:center !important;'
                . 'border-top:1px solid var(--tblr-border-color) !important;border-radius:0 !important;'
                . 'background:var(--tblr-bg-surface) !important;'
                . 'box-shadow:0 -10px 30px -20px rgba(15,23,42,.35);}'
                . 'body:has(form#main-form .form-button-separator:not(.modal *)) .page-body{padding-bottom:84px;}'
                . '@media print{' . $bar . '{position:static;box-shadow:none;}}';
        }

        return $css;
    }

    /**
     * Tabs as pills. The core tab rules (_tabs.scss) use !important on
     * borders/margins, hence the !important and the extra `body` here.
     */
    private const PILLS_CSS = <<<'CSS'
body .card-tabs #tabspanel.nav-tabs{gap:2px;padding:6px;border:0 !important;background:transparent;}
body .card-tabs #tabspanel.nav-tabs .nav-link,
body .card-tabs.vertical #tabspanel.nav-tabs .nav-link,
body .card-tabs.vertical #tabspanel.nav-tabs .nav-item:first-child .nav-link{
margin:0 !important;border:0 !important;border-radius:var(--gs-btn-radius) !important;
background:transparent;color:var(--tblr-secondary);font-weight:500;transition:background-color .15s ease,color .15s ease;}
body .card-tabs #tabspanel.nav-tabs .nav-link:hover{background:color-mix(in srgb,var(--tblr-body-color) 5%,transparent);color:var(--tblr-body-color);}
body .card-tabs #tabspanel.nav-tabs .nav-link.active,
body .card-tabs.vertical #tabspanel.nav-tabs .nav-link.active{
border:0 !important;border-left-width:0 !important;margin-right:0 !important;
background:color-mix(in srgb,var(--tblr-primary) 12%,var(--tblr-bg-surface));color:color-mix(in srgb,var(--tblr-primary) 85%,var(--tblr-body-color));font-weight:600;}
body .card-tabs > .tab-content.card{border-radius:var(--gs-card-radius) !important;}
CSS;
}
