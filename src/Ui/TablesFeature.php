<?php

namespace GlpiPlugin\Glpistyle\Ui;

/**
 * Search result lists (tickets, assets...): rows, header, pager.
 */
class TablesFeature extends Feature
{
    public const DENSITIES = [
        'auto'        => 'Automático (como o GLPI)',
        'compact'     => 'Compacto',
        'normal'      => 'Normal',
        'comfortable' => 'Confortável',
    ];

    public const HEADERS = [
        'plain'  => 'Simples (padrão do GLPI)',
        'caps'   => 'Maiúsculas discretas',
        'tinted' => 'Fundo levemente colorido',
    ];

    public const LINKS = [
        'glpi'    => 'Cor dos links (padrão do GLPI)',
        'neutral' => 'Cor do texto, destacando no hover',
    ];

    /** [cell padding, font size] */
    private const DENSITY_METRICS = [
        'compact'     => ['.3rem .5rem', '.78rem'],
        'normal'      => ['.55rem .75rem', 'var(--tblr-body-font-size)'],
        'comfortable' => ['.8rem 1rem', 'var(--tblr-body-font-size)'],
    ];

    public static function key(): string
    {
        return 'tables';
    }

    public static function order(): int
    {
        return 30;
    }

    public static function title(): string
    {
        return 'Listas e tabelas';
    }

    public static function subtitle(): string
    {
        return 'Listas de chamados, ativos e buscas: linhas, cabeçalho e paginação';
    }

    public static function icon(): string
    {
        return 'ti-table';
    }

    public static function tone(): string
    {
        return 'teal';
    }

    protected static function fields(): array
    {
        return [
            'ui_tables_density' => ['enum', 'auto', array_keys(self::DENSITIES)],
            'ui_tables_header'  => ['enum', 'caps', array_keys(self::HEADERS)],
            'ui_tables_hover'   => ['bool', 1],
            'ui_tables_striped' => ['bool', 1],
            'ui_tables_links'   => ['enum', 'glpi', array_keys(self::LINKS)],
            'ui_tables_rounded' => ['bool', 1],
        ];
    }

    public static function section(array $ui, array $config): string
    {
        return '<div class="gs-row gs-row--3">'
            . $ui['card']('ti-layout-rows', 'Linhas', $ui['select']('ui_tables_density', 'Densidade', self::DENSITIES, 'O GLPI compacta a lista sozinho em telas com menos de 1900px de largura. Escolha uma densidade para fixar o espaçamento.')
                . $ui['switch']('ui_tables_hover', 'Destacar a linha sob o mouse', 'Fundo na cor primária e uma barra à esquerda.')
                . $ui['switch']('ui_tables_striped', 'Linhas zebradas'))
            . $ui['card']('ti-table-column', 'Cabeçalho e links', $ui['select']('ui_tables_header', 'Cabeçalho', self::HEADERS)
                . $ui['select']('ui_tables_links', 'Links nas linhas', self::LINKS))
            . $ui['card']('ti-layout-cards', 'Contêiner', $ui['switch']('ui_tables_rounded', 'Cantos arredondados na lista', 'Usa o mesmo arredondamento dos cards quando "Cards e formulários" está ativo.'))
            . '</div>';
    }

    public static function css(array $config): string
    {
        // Same chain as the core rules in css/includes/pages/_search.scss,
        // plus `body`, so these win over its own compaction below 1900px
        $table = 'body .search_page .search-container .search-card .search-results.table';
        $table_any = '.page-body .search-results.table';

        $css = '';
        if (isset(self::DENSITY_METRICS[$config['ui_tables_density']])) {
            [$padding, $size] = self::DENSITY_METRICS[$config['ui_tables_density']];
            $css .= $table . ',' . $table_any . '{font-size:' . $size . ';}'
                . $table . ' > :not(caption) > * > *,' . $table_any . ' > :not(caption) > * > *{padding:' . $padding . ';}';
        }

        if ($config['ui_tables_header'] === 'caps') {
            $css .= $table_any . ' > thead th{color:var(--tblr-secondary);font-size:.72rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;}';
        } elseif ($config['ui_tables_header'] === 'tinted') {
            $css .= $table_any . ' > thead th{--tblr-table-bg:color-mix(in srgb,var(--tblr-primary) 7%,var(--tblr-bg-surface));'
                . 'background-color:color-mix(in srgb,var(--tblr-primary) 7%,var(--tblr-bg-surface)) !important;font-weight:600;}';
        }

        if ((int) $config['ui_tables_hover']) {
            $css .= $table_any . '{--tblr-table-hover-bg:color-mix(in srgb,var(--tblr-primary) 8%,transparent);}'
                . $table_any . '.table-hover > tbody > tr:hover > td:first-child{box-shadow:inset 3px 0 0 var(--tblr-primary),'
                . 'inset 0 0 0 9999px var(--tblr-table-bg-state,var(--tblr-table-bg-type,var(--tblr-table-accent-bg)));}';
        }

        if (!(int) $config['ui_tables_striped']) {
            $css .= $table_any . '{--tblr-table-striped-bg:transparent;}';
        }

        if ($config['ui_tables_links'] === 'neutral') {
            $css .= $table_any . ' > tbody a:not(.btn){color:var(--tblr-body-color);}'
                . $table_any . ' > tbody a:not(.btn):hover{color:var(--tblr-primary);}';
        }

        if ((int) $config['ui_tables_rounded']) {
            // clip (not hidden) keeps the sticky header/footer working
            $css .= '.page-body .search-card{border-radius:var(--gs-card-radius,var(--tblr-border-radius-lg)) !important;overflow:clip;}';
        }

        return $css;
    }
}
