<?php

namespace GlpiPlugin\Glpistyle\Ui;

use GlpiPlugin\Glpistyle\Style;

/**
 * Modern main menu: vertical sidebar and horizontal top bar.
 */
class MenuFeature extends Feature
{
    public const STYLES = [
        'soft'    => 'Pílula suave',
        'solid'   => 'Pílula sólida',
        'bar'     => 'Indicador lateral',
        'minimal' => 'Minimalista',
    ];

    public const SPACINGS = [
        'compact'     => 'Compacto',
        'normal'      => 'Normal',
        'comfortable' => 'Confortável',
    ];

    public const TOPBAR_STYLES = [
        'default'  => 'Cor sólida (padrão do GLPI)',
        'gradient' => 'Gradiente',
        'glass'    => 'Vidro translúcido',
    ];

    private const PADDING = ['compact' => '.32rem', 'normal' => '.5rem', 'comfortable' => '.7rem'];

    public static function key(): string
    {
        return 'menu';
    }

    public static function order(): int
    {
        return 10;
    }

    public static function title(): string
    {
        return 'Menu moderno';
    }

    public static function subtitle(): string
    {
        return 'Menu lateral e barra do topo: item ativo, hover e submenus';
    }

    public static function icon(): string
    {
        return 'ti-menu-2';
    }

    public static function tone(): string
    {
        return 'blue';
    }

    protected static function fields(): array
    {
        return [
            'ui_menu_style'         => ['enum', 'soft', array_keys(self::STYLES)],
            'ui_menu_active'        => ['color_opt', ''],
            'ui_menu_radius'        => ['int', 10, [0, 20]],
            'ui_menu_spacing'       => ['enum', 'normal', array_keys(self::SPACINGS)],
            'ui_topbar_style'       => ['enum', 'default', array_keys(self::TOPBAR_STYLES)],
            'ui_topbar_gradient_to' => ['color', '#6d5dfc'],
            'ui_topbar_shadow'      => ['bool', 1],
        ];
    }

    public static function section(array $ui, array $config): string
    {
        return '<div class="gs-row gs-row--3">'
            . $ui['card']('ti-hand-click', 'Item ativo', $ui['select']('ui_menu_style', 'Estilo', self::STYLES)
                . $ui['color_opt']('ui_menu_active', 'Cor de destaque', '#3b82f6'), 'Como a seção em que você está aparece no menu. O padrão do tema usa a cor primária.')
            . $ui['card']('ti-spacing-vertical', 'Forma e espaçamento', $ui['range']('ui_menu_radius', 'Arredondamento', 0, 20, 'px')
                . $ui['select']('ui_menu_spacing', 'Espaçamento dos itens', self::SPACINGS))
            . $ui['card']('ti-layout-navbar', 'Barra do topo', $ui['select']('ui_topbar_style', 'Estilo', self::TOPBAR_STYLES)
                . $ui['color']('ui_topbar_gradient_to', 'Segunda cor do gradiente')
                . $ui['switch']('ui_topbar_shadow', 'Sombra suave'), 'Com o menu na horizontal, a barra do topo é o próprio menu. O gradiente vai da cor "Menu - fundo" (seção Cores) até a segunda cor.')
            . '</div>';
    }

    public static function css(array $config): string
    {
        $custom = $config['ui_menu_active'];
        $accent = $custom !== '' ? $custom : 'var(--tblr-primary)';
        $accent_fg = $custom !== '' ? Style::contrast($custom) : 'var(--tblr-primary-fg, #ffffff)';
        $mixed = 'color-mix(in srgb, ' . $accent . ' 60%, var(--glpi-mainmenu-fg))';

        [$bg, $fg, $icon, $bar] = match ($config['ui_menu_style']) {
            'solid'   => [$accent, $accent_fg, $accent_fg, '0px'],
            'bar'     => ['color-mix(in srgb, var(--glpi-mainmenu-fg) 9%, transparent)', 'var(--glpi-mainmenu-fg)', $mixed, '3px'],
            'minimal' => ['transparent', 'var(--glpi-mainmenu-fg)', $mixed, '0px'],
            default   => ['color-mix(in srgb, ' . $accent . ' 24%, transparent)', 'var(--glpi-mainmenu-fg)', $mixed, '0px'],
        };

        $css = ':root{'
            . '--gs-menu-accent:' . $accent . ';'
            . '--gs-menu-active-bg:' . $bg . ';'
            . '--gs-menu-active-fg:' . $fg . ';'
            . '--gs-menu-active-icon:' . $icon . ';'
            . '--gs-menu-bar-w:' . $bar . ';'
            . '--gs-menu-radius:' . $config['ui_menu_radius'] . 'px;'
            . '--gs-menu-py:' . self::PADDING[$config['ui_menu_spacing']] . ';'
            . '}';

        $topbar = 'header.navbar.topbar';
        if ($config['ui_topbar_style'] === 'gradient') {
            $css .= $topbar . '{background:linear-gradient(100deg,var(--glpi-mainmenu-bg) 0%,' . $config['ui_topbar_gradient_to'] . ' 100%) !important;}';
        } elseif ($config['ui_topbar_style'] === 'glass') {
            $css .= $topbar . '{background:color-mix(in srgb,var(--glpi-mainmenu-bg) 78%,transparent) !important;'
                . '-webkit-backdrop-filter:blur(14px) saturate(160%);backdrop-filter:blur(14px) saturate(160%);}';
        }
        $css .= $topbar . '{box-shadow:' . ((int) $config['ui_topbar_shadow']
            ? '0 1px 0 rgba(15,23,42,.05),0 10px 28px -18px rgba(15,23,42,.45)'
            : 'none') . ' !important;}';

        return $css;
    }
}
