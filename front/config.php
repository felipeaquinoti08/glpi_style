<?php

use GlpiPlugin\Glpistyle\Config;
use GlpiPlugin\Glpistyle\Ui\Registry;

Session::checkRight('config', UPDATE);

global $CFG_GLPI;

$self_url = $CFG_GLPI['root_doc'] . '/plugins/glpistyle/front/config.php';

if (isset($_POST['update'])) {
    Config::saveFromInput($_POST);

    $errors = [];
    foreach (array_keys(Config::ASSETS) as $slot) {
        if (!empty($_POST['remove_' . $slot])) {
            Config::removeAsset($slot);
            continue;
        }
        if (($_FILES[$slot]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $error = Config::storeUpload($slot, $_FILES[$slot]);
            if ($error !== null) {
                $errors[] = $error;
            }
        }
    }

    foreach ($errors as $error) {
        Session::addMessageAfterRedirect(htmlspecialchars($error), false, ERROR);
    }
    Session::addMessageAfterRedirect(__('Aparência salva com sucesso.', 'glpistyle'));
    Html::redirect($self_url);
}

if (isset($_POST['reset'])) {
    Config::reset();
    Session::addMessageAfterRedirect(__('Configurações visuais restauradas para o padrão.', 'glpistyle'));
    Html::redirect($self_url);
}

$config = Config::get();
$plugin_url = $CFG_GLPI['root_doc'] . '/plugins/glpistyle';

$e = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

// ---------------------------------------------------------------------------
// Small renderers - every value printed goes through $e()
// ---------------------------------------------------------------------------

$help = static fn(string $text): string => $text === '' ? ''
    : '<span class="gs-help" tabindex="0" title="' . $e($text) . '" aria-label="' . $e($text) . '">?</span>';

$card = static function (string $icon, string $title, string $body, string $help_text = '', string $class = '') use ($e, $help): string {
    return '<div class="gs-card ' . $class . '"><div class="gs-card__title"><i class="ti ' . $icon . '"></i>' . $e($title) . $help($help_text) . '</div>'
        . $body . '</div>';
};

$section = static function (string $id, string $icon, string $tone, string $title, string $subtitle, string $body) use ($e): string {
    return '<details class="gs-section" id="gs-section-' . $id . '" data-section="' . $id . '" open>'
        . '<summary><span class="gs-section__badge gs-tone-' . $tone . '"><i class="ti ' . $icon . '"></i></span>'
        . '<span class="gs-section__head"><strong>' . $e($title) . '</strong><small>' . $e($subtitle) . '</small></span>'
        . '<i class="ti ti-chevron-up gs-section__chevron"></i></summary>'
        . '<div class="gs-section__body">' . $body . '</div></details>';
};

$label = static fn(string $for, string $text, string $help_text = ''): string =>
    '<label class="form-label" for="gs-' . $for . '">' . $e($text) . $help($help_text) . '</label>';

$switch = static function (string $name, string $text, string $help_text = '') use ($config, $e): string {
    return '<label class="form-check form-switch gs-switch">'
        . '<input class="form-check-input" type="checkbox" name="' . $name . '" value="1"' . ((int) $config[$name] ? ' checked' : '') . '>'
        . '<span class="form-check-label">' . $e($text)
        . ($help_text !== '' ? '<small>' . $e($help_text) . '</small>' : '') . '</span></label>';
};

$text = static function (string $name, string $text, int $max, string $placeholder = '', string $help_text = '') use ($config, $e, $label): string {
    return '<div class="gs-control">' . $label($name, $text, $help_text)
        . '<input class="form-control" type="text" id="gs-' . $name . '" name="' . $name . '" maxlength="' . $max . '"'
        . ' value="' . $e($config[$name]) . '" placeholder="' . $e($placeholder) . '"></div>';
};

$textarea = static function (string $name, string $text, int $rows, string $help_text = '') use ($config, $e, $label): string {
    return '<div class="gs-control">' . $label($name, $text, $help_text)
        . '<textarea class="form-control" id="gs-' . $name . '" name="' . $name . '" rows="' . $rows . '">' . $e($config[$name]) . '</textarea></div>';
};

$number = static function (string $name, string $text, int $min, int $max) use ($config, $e, $label): string {
    return '<div class="gs-control">' . $label($name, $text)
        . '<div class="input-group input-group-flat gs-number"><input class="form-control" type="number" id="gs-' . $name . '" name="' . $name . '"'
        . ' min="' . $min . '" max="' . $max . '" value="' . $e($config[$name]) . '"><span class="input-group-text">px</span></div></div>';
};

$select = static function (string $name, string $text, array $options, string $help_text = '', array $option_help = []) use ($config, $e, $label): string {
    $html = '<div class="gs-control">' . $label($name, $text, $help_text)
        . '<select class="form-select" id="gs-' . $name . '" name="' . $name . '">';
    foreach ($options as $value => $option_label) {
        $html .= '<option value="' . $e($value) . '"' . ($config[$name] === $value ? ' selected' : '')
            . (isset($option_help[$value]) ? ' data-help="' . $e($option_help[$value]) . '"' : '') . '>' . $e($option_label) . '</option>';
    }
    $html .= '</select>';
    if ($option_help !== []) {
        $html .= '<div class="form-text" data-select-help>' . $e($option_help[$config[$name]] ?? '') . '</div>';
    }
    return $html . '</div>';
};

$range = static function (string $name, string $text, int $min, int $max, string $unit, int $step = 1, string $help_text = '') use ($config, $e, $help): string {
    return '<div class="gs-control gs-range"><label class="form-label" for="gs-' . $name . '">' . $e($text) . $help($help_text)
        . '<output class="gs-pill" data-unit="' . $e($unit) . '">' . $e($config[$name] . $unit) . '</output></label>'
        . '<input class="form-range" type="range" id="gs-' . $name . '" name="' . $name . '" min="' . $min . '" max="' . $max . '" step="' . $step . '" value="' . $e($config[$name]) . '"></div>';
};

$color = static function (string $name, string $text = '') use ($config, $e): string {
    return '<div class="gs-control">' . ($text !== '' ? '<span class="form-label">' . $e($text) . '</span>' : '')
        . '<div class="gs-color"><input type="color" id="gs-' . $name . '" name="' . $name . '" value="' . $e($config[$name]) . '" aria-label="' . $e($text) . '">'
        . '<code data-color-label>' . $e($config[$name]) . '</code></div></div>';
};

/** Color that may be left on "Usar padrão do tema" ('' in the config) */
$color_opt = static function (string $name, string $text, string $fallback) use ($config, $e): string {
    $is_default = $config[$name] === '';
    return '<div class="gs-control">' . ($text !== '' ? '<span class="form-label">' . $e($text) . '</span>' : '')
        . '<div class="gs-color gs-color--opt' . ($is_default ? ' is-default' : '') . '">'
        . '<input type="color" id="gs-' . $name . '" name="' . $name . '" value="' . $e($is_default ? $fallback : $config[$name]) . '" aria-label="' . $e($text) . '">'
        . '<label class="form-check gs-color__default"><input class="form-check-input" type="checkbox" name="' . $name . Config::DEFAULT_SUFFIX . '" value="1"' . ($is_default ? ' checked' : '') . '>'
        . '<span class="form-check-label">Usar padrão do tema</span></label></div></div>';
};

$upload = static function (string $slot, string $hint, string $extra = '') use ($config, $e): string {
    [, $extensions] = Config::ASSETS[$slot];
    $url = Config::getAssetPath($slot, $config) !== null ? Config::getAssetUrl($slot, $config) : '';
    $accept = implode(',', array_map(static fn($ext) => '.' . $ext, $extensions));

    return '<div class="gs-upload" data-upload>'
        . '<div class="gs-upload__preview' . ($url !== '' ? ' has-image' : '') . '">'
        . ($url !== '' ? '<img src="' . $e($url) . '" alt="">' : '<span class="gs-upload__empty"><i class="ti ti-photo-plus"></i>' . $e($hint) . '</span>')
        . '</div>'
        . ($url !== '' ? '<label class="form-check gs-upload__remove"><input class="form-check-input" type="checkbox" name="remove_' . $slot . '" value="1"><span class="form-check-label">Remover</span></label>' : '')
        . '<input class="form-control" type="file" name="' . $slot . '" accept="' . $e($accept) . '">'
        . '<small class="gs-upload__pending" hidden><i class="ti ti-info-circle"></i> Nova imagem selecionada. Ela entra na prévia depois de salvar.</small>'
        . $extra . '</div>';
};

$position_picker = static function () use ($config, $e): string {
    $html = '<div class="gs-position" role="radiogroup" aria-label="Posição na tela"><div class="gs-position__grid">';
    foreach (['tl', 'tc', 'tr', 'ml', 'mc', 'mr', 'bl', 'bc', 'br'] as $pos) {
        $html .= '<label class="gs-position__cell" title="' . $e(Config::POSITIONS[$pos]) . '">'
            . '<input type="radio" name="login_position" value="' . $pos . '" data-label="' . $e(Config::POSITIONS[$pos]) . '"' . ($config['login_position'] === $pos ? ' checked' : '') . '>'
            . '<span></span></label>';
    }
    $html .= '</div><div class="gs-position__panels">';
    foreach (['panel_left', 'panel_right'] as $pos) {
        $html .= '<label class="gs-position__panel gs-position__panel--' . $pos . '" title="' . $e(Config::POSITIONS[$pos]) . '">'
            . '<input type="radio" name="login_position" value="' . $pos . '" data-label="' . $e(Config::POSITIONS[$pos]) . '"' . ($config['login_position'] === $pos ? ' checked' : '') . '>'
            . '<span></span></label>';
    }
    $html .= '</div><div class="gs-position__label" data-position-label>' . $e(Config::POSITIONS[$config['login_position']]) . '</div></div>';
    return $html;
};

$fit_help = array_map(static fn($fit) => $fit[1], Config::BG_FITS);
$fits = array_map(static fn($fit) => $fit[0], Config::BG_FITS);

// ---------------------------------------------------------------------------
// Page
// ---------------------------------------------------------------------------

Html::header('GLPI Style', $_SERVER['PHP_SELF'], 'config', 'plugin');

echo '<link rel="stylesheet" href="' . $e($plugin_url . '/' . plugin_glpistyle_resource('config.css', 'css/config.css')) . '">';

echo '<div class="gs-editor" id="gs-editor" data-preview-url="' . $e($plugin_url . '/front/preview.php') . '"'
    . ' data-live-css-url="' . $e($plugin_url . '/front/live.css.php') . '">';

echo '<form class="gs-editor__form" id="gs-editor-form" method="post" action="' . $e($self_url) . '" enctype="multipart/form-data">';

echo '<header class="gs-editor__intro">';
echo '<div><h1>Identidade visual</h1><p>Personalize a tela de login, os logos, o favicon e as cores do GLPI.</p></div>';
echo '<button type="button" class="btn btn-outline-primary" data-preview-toggle><i class="ti ti-eye"></i> <span>Prévia ao vivo</span></button>';
echo '</header>';

// --- Login ---------------------------------------------------------------
$login = '';
$login .= '<div class="gs-card gs-card--flat">' . $switch('login_enabled', 'Ativar a nova tela de login', 'Desativado, o GLPI volta à tela de login padrão. Logos, favicon e cores continuam valendo.') . '</div>';

$login .= '<div class="gs-row gs-row--2">';
$login .= $card('ti-photo', 'Logo do login', $upload(
    'login_logo',
    'PNG ou SVG com fundo transparente',
    '<div class="gs-row gs-row--2 gs-row--tight">' . $number('login_logo_width', 'Largura', 40, 480) . $number('login_logo_height', 'Altura', 20, 240) . '</div>'
    . $select('login_logo_position', 'Posição do logo', Config::LOGO_POSITIONS, 'Nas caixas flutuantes, o logo pode ficar dentro da caixa ou acima dela, direto sobre o fundo.')
), 'Se vazio, usa o logo do menu (seção Página interna) ou o logo do GLPI.');
$login .= $card('ti-background', 'Background do login', $upload(
    'login_bg',
    'JPG, PNG ou WEBP, 1920×1080 ou maior',
    '<div class="gs-row gs-row--2 gs-row--tight">' . $select('login_bg_fit', 'Ajuste da imagem', $fits, '', $fit_help)
    . $select('login_bg_position', 'Ponto de foco', Config::BG_POSITIONS, 'Parte da imagem que fica sempre visível quando ela é cortada.') . '</div>'
), 'Sem imagem, o fundo é o gradiente da seção "Fundo e efeitos".');
$login .= '</div>';

$login .= '<div class="gs-row gs-row--3">';
$login .= $card('ti-layout-board', 'Posição na tela', $position_picker()
    . $select('login_box_width', 'Largura da caixa', Config::BOX_WIDTHS), 'Escolha um ponto da grade para uma caixa flutuante sobre o fundo, ou um painel lateral de altura total.');
$login .= $card('ti-droplet', 'Fundo da caixa', $color_opt('login_box_bg', '', '#ffffff')
    . $range('login_box_opacity', 'Opacidade do fundo', 0, 100, '%', 5)
    . $range('login_box_blur', 'Vidro fosco', 0, 40, 'px', 1, 'Desfoca o que está atrás da caixa. Combine com opacidade abaixo de 100%.'), 'Cor, transparência e efeito de vidro da caixa (ou do painel lateral).');
$login .= $card('ti-typography', 'Cores do texto', $color_opt('login_title_color', 'Título', '#0f172a')
    . $color_opt('login_text_color', 'Textos e rótulos', '#334155'), 'O padrão do tema segue o tema claro ou escuro escolhido em "Formulário".');
$login .= '</div>';

$login .= '<div class="gs-row gs-row--2">';
$login .= $card('ti-align-left', 'Textos da caixa', '<div class="gs-row gs-row--2 gs-row--tight">'
    . $text('form_title', 'Título', 80, 'Central de Serviços')
    . $text('form_subtitle', 'Mensagem', 160, 'Acesso restrito a colaboradores')
    . $text('button_text', 'Texto do botão', 40, 'Entrar')
    . $text('extra_separator', 'Separador de outros logins', 40, 'ou continue com', 'Aparece acima de botões de outros plugins de login, como o SSO Microsoft.')
    . '</div>', 'Deixe o título em branco para manter o texto padrão do GLPI.');
$login .= $card('ti-forms', 'Formulário', '<div class="gs-row gs-row--2 gs-row--tight">'
    . $select('login_form_theme', 'Tema', Config::FORM_THEMES, 'Define as cores padrão de campos, textos e caixa.')
    . $select('login_font', 'Fonte', array_map(static fn($f) => $f[0], Config::FONTS))
    . $color('login_accent', 'Cor do botão e destaques')
    . $select('login_button_style', 'Estilo do botão', Config::BUTTON_STYLES)
    . $select('login_text_align', 'Alinhamento', Config::TEXT_ALIGNS)
    . $range('login_radius', 'Arredondamento', 0, 28, 'px')
    . '</div>'
    . '<div class="gs-row gs-row--2 gs-row--tight">' . $switch('login_title_divider', 'Linha abaixo do título') . $switch('login_password_toggle', 'Botão de mostrar senha') . '</div>');
$login .= '</div>';

$login .= '<div class="gs-row gs-row--2">';
$login .= $card('ti-layout-bottombar', 'Modo do rodapé', $select('footer_mode', '', Config::FOOTER_MODES)
    . $text('footer_text', 'Texto personalizado', 160, '© Sua empresa - Todos os direitos reservados'));
$login .= '</div>';

echo $section('login', 'ti-login-2', 'orange', 'Tela de login', 'Logo, fundo, posição e textos da caixa de acesso', $login);

// --- Background effects --------------------------------------------------
$bg = '<div class="gs-row gs-row--2">';
$bg .= $card('ti-color-swatch', 'Gradiente', '<div class="gs-row gs-row--3 gs-row--tight">'
    . $color('login_bg_color1', 'Cor 1') . $color('login_bg_color2', 'Cor 2') . $color('login_bg_color3', 'Cor 3') . '</div>'
    . $range('login_bg_angle', 'Ângulo', 0, 360, '°', 5)
    . $range('login_overlay_opacity', 'Gradiente sobre a imagem de fundo', 0, 100, '%', 5, 'Com 0% a imagem aparece pura. Sem imagem de fundo, o gradiente é sempre exibido.'), 'Fundo usado quando não há imagem, ou aplicado por cima dela.');
$bg .= $card('ti-sparkles', 'Efeitos', $select('login_pattern', 'Textura', Config::PATTERNS)
    . $switch('login_shapes', 'Formas de luz desfocadas')
    . $switch('login_bg_animated', 'Animação suave', 'Gradiente em movimento e formas flutuando. Desligada automaticamente para quem prefere menos movimento.'), 'Textura e formas só aparecem quando o gradiente está visível.');
$bg .= '</div>';
echo $section('background', 'ti-palette', 'purple', 'Fundo e efeitos', 'Gradiente, textura e animação do fundo do login', $bg);

// --- Hero ----------------------------------------------------------------
$hero = '<div class="gs-row gs-row--2">';
$hero .= $card('ti-speakerphone', 'Textos', $text('hero_badge', 'Selo', 60, 'Central de Serviços de TI')
    . $text('hero_title', 'Título', 120)
    . $textarea('hero_subtitle', 'Subtítulo', 3));
$hero .= $card('ti-list-check', 'Destaques', $textarea('hero_features', 'Um por linha (até 6)', 5)
    . $color('hero_text_color', 'Cor dos textos'), 'Deixe todos os campos em branco para mostrar apenas a imagem.');
$hero .= '</div>';
echo $section('hero', 'ti-layout-sidebar-right', 'teal', 'Painel de destaque', 'Textos exibidos sobre o fundo nos layouts de painel lateral, em telas grandes', $hero);

// --- Internal pages ------------------------------------------------------
$internal = '<div class="gs-row gs-row--3">';
$internal .= $card('ti-photo', 'Logo do cabeçalho', $upload(
    'logo_full',
    'Horizontal, ~200×110 px',
    '<div class="gs-row gs-row--2 gs-row--tight">' . $number('logo_full_width', 'Largura', 40, 240) . $number('logo_full_height', 'Altura', 20, 120) . '</div>'
), 'Logo no topo do menu lateral.');
$internal .= $card('ti-layout-sidebar-left-collapse', 'Logo do menu recolhido', $upload('logo_reduced', 'Quadrado, ~80×80 px'), 'Se vazio, usa o logo do cabeçalho reduzido.');
$internal .= $card('ti-world-www', 'Favicon', $upload('favicon', 'ICO, PNG ou SVG quadrado'), 'Ícone da aba do navegador, 32×32 ou 64×64.');
$internal .= '</div>';
echo $section('internal', 'ti-apps', 'orange', 'Página interna', 'Logos do menu lateral e favicon', $internal);

// --- Colors --------------------------------------------------------------
$colors = '<div class="gs-row gs-row--3"><div class="gs-control gs-preset">'
    . '<label class="form-label" for="gs-preset"><i class="ti ti-color-filter"></i> Preset de cores</label>'
    . '<select class="form-select" id="gs-preset" data-preset><option value="">- Selecione um preset -</option></select></div></div>';
$colors .= '<div class="gs-row gs-row--3">';
foreach ([
    'color_primary'   => ['Cor primária', 'Botões principais, abas e destaques.', '#2f6fed'],
    'color_secondary' => ['Cor secundária', 'Botões secundários e textos de apoio.', '#64748b'],
    'color_links'     => ['Cor dos links', '', '#2f6fed'],
    'color_menu_bg'   => ['Menu - fundo', 'Menu lateral, ou a barra do topo quando o menu está na horizontal.', '#1e293b'],
    'color_menu_fg'   => ['Menu - texto', 'Menu lateral, ou a barra do topo quando o menu está na horizontal.', '#ffffff'],
    'color_header_bg' => ['Cabeçalho - fundo', 'Barra de busca/usuário acima do conteúdo. Só existe com o menu na vertical; com o menu horizontal, use as cores do menu.', '#ffffff'],
    'color_header_fg' => ['Cabeçalho - texto', 'Só existe com o menu na vertical; com o menu horizontal, use as cores do menu.', '#1e293b'],
] as $name => [$title, $help_text, $fallback]) {
    $colors .= $card('', $title, $color_opt($name, '', $fallback), $help_text, 'gs-card--color');
}
$colors .= '</div>';
echo $section('colors', 'ti-brush', 'purple', 'Cores', 'Aplicadas por cima do tema de cores escolhido por cada usuário', $colors);

// --- Interface improvements (src/Ui/*Feature.php) -------------------------
$ui = compact('card', 'switch', 'text', 'textarea', 'number', 'select', 'range', 'color', 'color_opt', 'help', 'e');
foreach (Registry::all() as $feature) {
    $body = '<div class="gs-card gs-card--flat">'
        . $switch($feature::enabledField(), 'Ativar esta melhoria', 'Desligada, esta parte do GLPI fica exatamente como o GLPI desenha.')
        . '</div>'
        . '<div class="gs-feature-body">' . $feature::section($ui, $config) . '</div>';
    echo $section('ui-' . $feature::key(), $feature::icon(), $feature::tone(), $feature::title(), $feature::subtitle(), $body);
}

echo '<div class="gs-savebar">';
echo '<button type="submit" name="reset" value="1" class="btn btn-ghost-secondary" formnovalidate data-confirm="Restaurar cores, textos e posições para o padrão? As imagens enviadas são mantidas."><i class="ti ti-restore"></i> Restaurar padrão</button>';
echo '<button type="submit" name="update" value="1" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Salvar</button>';
echo '</div>';

Html::closeForm();

// --- Preview drawer -----------------------------------------------------
echo '<aside class="gs-preview" id="gs-preview" aria-label="Prévia ao vivo">';
echo '<div class="gs-preview__toolbar">';
echo '<span class="gs-preview__title"><span class="gs-preview__live"></span> Prévia da tela de login</span>';
echo '<div class="btn-group" role="group" aria-label="Dispositivo">';
foreach (['desktop' => ['ti-device-desktop', 'Computador'], 'tablet' => ['ti-device-tablet', 'Tablet'], 'mobile' => ['ti-device-mobile', 'Celular']] as $device => [$icon, $device_label]) {
    echo '<button type="button" class="btn btn-sm btn-outline-secondary' . ($device === 'desktop' ? ' active' : '') . '" data-device="' . $device . '" title="' . $device_label . '" aria-label="' . $device_label . '"><i class="ti ' . $icon . '"></i></button>';
}
echo '</div>';
echo '<a class="btn btn-sm btn-ghost-secondary btn-icon" id="gs-preview-open" href="' . $e($plugin_url . '/front/preview.php') . '" target="_blank" rel="noopener" title="Abrir em tela cheia"><i class="ti ti-external-link"></i></a>';
echo '<button type="button" class="btn btn-sm btn-ghost-secondary btn-icon" data-preview-toggle title="Fechar prévia" aria-label="Fechar prévia"><i class="ti ti-x"></i></button>';
echo '</div>';
echo '<div class="gs-preview__stage" id="gs-preview-stage"><div class="gs-preview__device" id="gs-preview-device"></div></div>';
echo '</aside>';

echo '</div>';

echo '<script src="' . $e($plugin_url . '/' . plugin_glpistyle_resource('config.js', 'js/config.js')) . '"></script>';

Html::footer();
