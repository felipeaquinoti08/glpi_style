<?php

use GlpiPlugin\Glpistyle\Config;

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
    // Back to the default look; uploaded images are kept
    Config::saveFromInput(Config::defaults());
    Session::addMessageAfterRedirect(__('Configurações visuais restauradas para o padrão.', 'glpistyle'));
    Html::redirect($self_url);
}

$config = Config::get();
$plugin_url = $CFG_GLPI['root_doc'] . '/plugins/glpistyle';
$version = PLUGIN_GLPISTYLE_VERSION;

$e = static fn($s): string => htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$switch = static function (string $name, string $label, string $help = '') use ($config, $e): string {
    return '<label class="form-check form-switch gs-switch">'
        . '<input class="form-check-input" type="checkbox" name="' . $name . '" value="1"' . ((int) $config[$name] ? ' checked' : '') . '>'
        . '<span class="form-check-label"><strong>' . $e($label) . '</strong>'
        . ($help !== '' ? '<small>' . $e($help) . '</small>' : '') . '</span></label>';
};

$text = static function (string $name, string $label, int $max, string $placeholder = '') use ($config, $e): string {
    return '<div class="gs-control"><label class="form-label" for="gs-' . $name . '">' . $e($label) . '</label>'
        . '<input class="form-control" type="text" id="gs-' . $name . '" name="' . $name . '" maxlength="' . $max . '"'
        . ' value="' . $e($config[$name]) . '" placeholder="' . $e($placeholder) . '"></div>';
};

$textarea = static function (string $name, string $label, int $rows, string $help = '') use ($config, $e): string {
    return '<div class="gs-control"><label class="form-label" for="gs-' . $name . '">' . $e($label) . '</label>'
        . '<textarea class="form-control" id="gs-' . $name . '" name="' . $name . '" rows="' . $rows . '">' . $e($config[$name]) . '</textarea>'
        . ($help !== '' ? '<div class="form-text">' . $e($help) . '</div>' : '') . '</div>';
};

$color = static function (string $name, string $label) use ($config, $e): string {
    return '<div class="gs-control gs-color"><label class="form-label" for="gs-' . $name . '">' . $e($label) . '</label>'
        . '<div class="gs-color__field"><input type="color" id="gs-' . $name . '" name="' . $name . '" value="' . $e($config[$name]) . '">'
        . '<code data-color-label>' . $e($config[$name]) . '</code></div></div>';
};

$range = static function (string $name, string $label, int $min, int $max, string $unit, int $step = 1) use ($config, $e): string {
    return '<div class="gs-control gs-range"><label class="form-label" for="gs-' . $name . '">' . $e($label)
        . '<output data-unit="' . $e($unit) . '">' . $e($config[$name] . $unit) . '</output></label>'
        . '<input class="form-range" type="range" id="gs-' . $name . '" name="' . $name . '" min="' . $min . '" max="' . $max . '" step="' . $step . '" value="' . $e($config[$name]) . '"></div>';
};

$select = static function (string $name, string $label, array $options) use ($config, $e): string {
    $html = '<div class="gs-control"><label class="form-label" for="gs-' . $name . '">' . $e($label) . '</label>'
        . '<select class="form-select" id="gs-' . $name . '" name="' . $name . '">';
    foreach ($options as $value => $option_label) {
        $html .= '<option value="' . $e($value) . '"' . ($config[$name] === $value ? ' selected' : '') . '>' . $e($option_label) . '</option>';
    }
    return $html . '</select></div>';
};

$upload = static function (string $slot, string $help) use ($config, $e): string {
    [$label, $extensions] = Config::ASSETS[$slot];
    $url = Config::getAssetPath($slot, $config) !== null ? Config::getAssetUrl($slot, $config) : '';
    $accept = implode(',', array_map(static fn($ext) => '.' . $ext, $extensions));

    return '<div class="gs-upload' . ($url !== '' ? ' has-image' : '') . '" data-upload>'
        . '<div class="gs-upload__thumb">' . ($url !== '' ? '<img src="' . $e($url) . '" alt="">' : '<i class="ti ti-photo"></i>') . '</div>'
        . '<div class="gs-upload__body"><strong>' . $e($label) . '</strong><small>' . $e($help) . '</small>'
        . '<div class="gs-upload__actions">'
        . '<label class="btn btn-sm btn-outline-primary"><i class="ti ti-upload"></i> ' . ($url !== '' ? 'Trocar' : 'Enviar')
        . '<input type="file" name="' . $slot . '" accept="' . $e($accept) . '" hidden></label>'
        . ($url !== '' ? '<label class="form-check gs-upload__remove"><input class="form-check-input" type="checkbox" name="remove_' . $slot . '" value="1"><span class="form-check-label">Remover</span></label>' : '')
        . '</div><small class="gs-upload__pending" hidden>Nova imagem selecionada. Aparece na prévia depois de salvar.</small></div></div>';
};

Html::header('GLPI Style', $_SERVER['PHP_SELF'], 'config', 'plugin');

echo '<link rel="stylesheet" href="' . $e($plugin_url . '/css/config.css?v=' . $version) . '">';

echo '<div class="gs-editor" id="gs-editor" data-preview-url="' . $e($plugin_url . '/front/preview.php') . '">';

echo '<form class="gs-editor__form" id="gs-editor-form" method="post" action="' . $e($self_url) . '" enctype="multipart/form-data">';

echo '<header class="gs-editor__intro">';
echo '<div><h1>Aparência do GLPI</h1><p>Personalize a tela de login, os logos e o favicon. A prévia ao lado é atualizada enquanto você edita.</p></div>';
echo '</header>';

// --- Layout -------------------------------------------------------------
echo '<section class="card gs-section"><div class="card-body">';
echo '<h2><i class="ti ti-layout"></i> Layout da tela de login</h2>';
echo $switch('login_enabled', 'Ativar a nova tela de login', 'Desativado, o GLPI volta a exibir a tela de login padrão. Logos e favicon continuam valendo.');

echo '<div class="gs-layouts" role="radiogroup" aria-label="Layout">';
foreach (Config::LAYOUTS as $value => $label) {
    echo '<label class="gs-layout-option">'
        . '<input type="radio" name="login_layout" value="' . $e($value) . '"' . ($config['login_layout'] === $value ? ' checked' : '') . '>'
        . '<span class="gs-layout-thumb gs-layout-thumb--' . $e($value) . '"><span class="gs-lt-hero"></span><span class="gs-lt-form"><i></i><i></i><i></i></span></span>'
        . '<span class="gs-layout-label">' . $e($label) . '</span></label>';
}
echo '</div>';

echo '<div class="gs-grid">';
echo $select('login_form_theme', 'Tema do formulário', Config::FORM_THEMES);
echo $select('login_font', 'Fonte', array_map(static fn($f) => $f[0], Config::FONTS));
echo $color('login_accent', 'Cor de destaque (botão, foco, links)');
echo $range('login_radius', 'Arredondamento dos cantos', 0, 28, 'px');
echo '</div>';
echo '</div></section>';

// --- Background ---------------------------------------------------------
echo '<section class="card gs-section"><div class="card-body">';
echo '<h2><i class="ti ti-palette"></i> Fundo</h2>';
echo '<div class="gs-grid gs-grid--3">';
echo $color('login_bg_color1', 'Cor 1');
echo $color('login_bg_color2', 'Cor 2');
echo $color('login_bg_color3', 'Cor 3');
echo '</div>';
echo '<div class="gs-grid">';
echo $range('login_bg_angle', 'Ângulo do gradiente', 0, 360, '°', 5);
echo $select('login_pattern', 'Textura decorativa', Config::PATTERNS);
echo '</div>';
echo $upload('login_bg', 'Opcional. Foto em JPG, PNG ou WEBP; recomendado 1920×1080 ou maior. O gradiente fica por cima dela.');
echo $range('login_overlay_opacity', 'Intensidade do gradiente sobre a foto', 0, 100, '%', 5);
echo '<div class="gs-grid">';
echo $switch('login_bg_animated', 'Animação suave', 'Gradiente em movimento e formas flutuando.');
echo $switch('login_shapes', 'Formas de luz desfocadas');
echo '</div>';
echo '</div></section>';

// --- Hero ---------------------------------------------------------------
echo '<section class="card gs-section"><div class="card-body">';
echo '<h2><i class="ti ti-sparkles"></i> Textos do painel de destaque</h2>';
echo '<p class="gs-section__hint">Aparecem nos layouts divididos, em telas grandes.</p>';
echo $text('hero_badge', 'Selo', 60, 'Central de Serviços de TI');
echo $text('hero_title', 'Título', 120);
echo $textarea('hero_subtitle', 'Subtítulo', 3);
echo $textarea('hero_features', 'Destaques', 4, 'Um por linha, até 6. Deixe em branco para ocultar.');
echo $color('hero_text_color', 'Cor dos textos');
echo '</div></section>';

// --- Form ---------------------------------------------------------------
echo '<section class="card gs-section"><div class="card-body">';
echo '<h2><i class="ti ti-forms"></i> Formulário</h2>';
echo '<div class="gs-grid">';
echo $text('form_title', 'Título', 80, 'Bem-vindo de volta');
echo $text('button_text', 'Texto do botão', 40, 'Entrar');
echo '</div>';
echo $text('form_subtitle', 'Subtítulo', 160);
echo '<div class="gs-grid">';
echo $text('extra_separator', 'Separador antes de outros logins (ex.: SSO)', 40, 'ou continue com');
echo $text('footer_text', 'Texto do rodapé', 160, '© Sua empresa');
echo '</div>';
echo '<div class="gs-grid">';
echo $switch('login_password_toggle', 'Botão para mostrar a senha');
echo $switch('login_hide_copyright', 'Ocultar o copyright do GLPI');
echo '</div>';
echo '</div></section>';

// --- Logos --------------------------------------------------------------
echo '<section class="card gs-section"><div class="card-body">';
echo '<h2><i class="ti ti-photo-star"></i> Logos e favicon</h2>';
echo $upload('login_logo', 'PNG/SVG com fundo transparente. Se vazio, usa o logo do menu.');
echo $range('login_logo_height', 'Altura do logo no login', 24, 160, 'px', 2);
echo $upload('logo_full', 'Menu lateral expandido. Proporção horizontal, cerca de 200×110 px.');
echo $upload('logo_reduced', 'Menu lateral recolhido. Quadrado, cerca de 80×80 px. Se vazio, usa o logo expandido.');
echo $upload('favicon', 'Ícone da aba do navegador. ICO, PNG ou SVG quadrado (32×32 ou 64×64).');
echo '</div></section>';

echo '<div class="gs-savebar">';
echo '<button type="submit" name="reset" value="1" class="btn btn-ghost-secondary" formnovalidate data-confirm="Restaurar cores, textos e layout para o padrão? As imagens enviadas são mantidas."><i class="ti ti-restore"></i> Restaurar padrão</button>';
echo '<button type="submit" name="update" value="1" class="btn btn-primary"><i class="ti ti-device-floppy"></i> Salvar alterações</button>';
echo '</div>';

Html::closeForm();

// --- Preview ------------------------------------------------------------
echo '<aside class="gs-editor__preview">';
echo '<div class="gs-preview__toolbar">';
echo '<span class="gs-preview__title"><span class="gs-preview__live"></span> Prévia ao vivo</span>';
echo '<div class="btn-group" role="group" aria-label="Dispositivo">';
foreach (['desktop' => ['ti-device-desktop', 'Computador'], 'tablet' => ['ti-device-tablet', 'Tablet'], 'mobile' => ['ti-device-mobile', 'Celular']] as $device => [$icon, $label]) {
    echo '<button type="button" class="btn btn-sm btn-outline-secondary' . ($device === 'desktop' ? ' active' : '') . '" data-device="' . $device . '" title="' . $label . '" aria-label="' . $label . '"><i class="ti ' . $icon . '"></i></button>';
}
echo '</div>';
echo '<a class="btn btn-sm btn-ghost-secondary" id="gs-preview-open" href="' . $e($plugin_url . '/front/preview.php') . '" target="_blank" rel="noopener" title="Abrir em tela cheia"><i class="ti ti-external-link"></i></a>';
echo '</div>';
echo '<div class="gs-preview__stage" id="gs-preview-stage"><div class="gs-preview__device" id="gs-preview-device">';
echo '<iframe id="gs-preview-frame" title="Prévia da tela de login" src="' . $e($plugin_url . '/front/preview.php') . '"></iframe>';
echo '</div></div>';
echo '</aside>';

echo '</div>';

echo '<script src="' . $e($plugin_url . '/js/config.js?v=' . $version) . '"></script>';

Html::footer();
