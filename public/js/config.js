/*
 * GLPI Style - config page: preview drawer and form helpers.
 *
 * The drawer shows the unsaved values on the login page or on real
 * internal pages (see the preview section). A new iframe is loaded in the
 * background and swapped in once ready, so it never flashes blank. The
 * editor page itself always keeps the saved look.
 */
(function () {
    'use strict';

    const editor = document.getElementById('gs-editor');
    const form = document.getElementById('gs-editor-form');
    const drawer = document.getElementById('gs-preview');
    const stage = document.getElementById('gs-preview-stage');
    const device = document.getElementById('gs-preview-device');
    const openLink = document.getElementById('gs-preview-open');
    if (!editor || !form || !drawer || !stage || !device) {
        return;
    }

    const store = {
        get(key) {
            try {
                return window.localStorage.getItem('glpistyle.' + key);
            } catch (e) {
                return null;
            }
        },
        set(key, value) {
            try {
                window.localStorage.setItem('glpistyle.' + key, value);
            } catch (e) {
                // Private mode / blocked storage: just not remembered
            }
        },
    };

    const PRESETS = {
        'Oceano':    ['#0ea5e9', '#475569', '#0284c7', '#0c4a6e', '#ffffff', '#ffffff', '#0f172a'],
        'Grafite':   ['#334155', '#64748b', '#2563eb', '#0f172a', '#e2e8f0', '#ffffff', '#0f172a'],
        'Esmeralda': ['#10b981', '#475569', '#059669', '#064e3b', '#ecfdf5', '#ffffff', '#064e3b'],
        'Violeta':   ['#7c3aed', '#6b7280', '#6d28d9', '#2e1065', '#f5f3ff', '#ffffff', '#2e1065'],
        'Laranja':   ['#f97316', '#57534e', '#ea580c', '#1c1917', '#fafaf9', '#ffffff', '#1c1917'],
        'Vermelho':  ['#dc2626', '#52525b', '#b91c1c', '#450a0a', '#fef2f2', '#ffffff', '#450a0a'],
        'Padrão do tema': null,
    };
    const PRESET_FIELDS = ['color_primary', 'color_secondary', 'color_links', 'color_menu_bg', 'color_menu_fg', 'color_header_bg', 'color_header_fg'];

    const previewUrl = editor.dataset.previewUrl;
    const liveCssUrl = editor.dataset.liveCssUrl;
    const devices = {
        desktop: [1440, 900],
        tablet: [834, 1112],
        mobile: [390, 844],
    };
    let current = 'desktop';
    let dirty = false;
    const dirtyStatus = form.querySelector('[data-dirty-status]');

    function setDirty(value) {
        dirty = value;
        if (dirtyStatus) {
            dirtyStatus.hidden = !value;
        }
    }

    // ---------------------------------------------------------------- preview
    //
    // Closed on every page load: nothing is requested until "Prévia" is
    // clicked. Targets:
    // - login: front/preview.php renders the core login template with the
    //   unsaved values (query string); reloaded on each change.
    // - internal pages: the real GLPI page, same origin, in which the CSS
    //   computed by front/live.css.php replaces the plugin's stylesheets.
    //   Changes only swap that stylesheet, the page is not reloaded.

    const targetSelect = drawer.querySelector('[data-preview-target]');
    const note = drawer.querySelector('[data-preview-note]');
    const PLUGIN_STYLESHEETS = 'link[rel="stylesheet"][href*="/plugins/glpistyle/front/style.css.php"],'
        + 'link[rel="stylesheet"][href*="/plugins/glpistyle/front/resource.php?f=ui-"]';

    function buildParams() {
        const params = new URLSearchParams();
        params.set('_live', '1');
        new FormData(form).forEach(function (value, name) {
            if (value instanceof File || name.startsWith('remove_') || name.startsWith('_glpi_')) {
                return;
            }
            params.append(name, value);
        });
        return params.toString();
    }

    function target() {
        return targetSelect ? targetSelect.value : 'login';
    }

    function targetUrl() {
        const option = targetSelect && targetSelect.selectedOptions[0];
        return option ? option.dataset.url : previewUrl;
    }

    function isOpen() {
        return editor.classList.contains('has-preview');
    }

    function fit() {
        if (!isOpen()) {
            return;
        }
        const [w, h] = devices[current];
        const scale = Math.min((stage.clientWidth - 32) / w, (stage.clientHeight - 32) / h, 1);
        device.style.width = Math.round(w * scale) + 'px';
        device.style.height = Math.round(h * scale) + 'px';
        device.querySelectorAll('iframe').forEach(function (frame) {
            frame.style.width = w + 'px';
            frame.style.height = h + 'px';
            frame.style.transform = 'scale(' + scale + ')';
        });
    }

    /** Swaps the unsaved-values stylesheet inside an internal page */
    function applyLiveCss(doc) {
        if (!doc || !doc.head || !liveCssUrl) {
            return;
        }
        const next = doc.createElement('link');
        next.rel = 'stylesheet';
        next.href = liveCssUrl + '?' + buildParams();
        next.dataset.gsLive = '1';
        next.addEventListener('load', function () {
            doc.querySelectorAll(PLUGIN_STYLESHEETS).forEach(function (link) {
                link.disabled = true;
            });
            doc.querySelectorAll('link[data-gs-live]').forEach(function (link) {
                if (link !== next) {
                    link.remove();
                }
            });
        });
        doc.head.appendChild(next);
    }

    /**
     * The internal preview is a real, logged-in GLPI page: hovering is fine,
     * but following links or submitting forms from it must not happen.
     */
    function makeInert(doc) {
        doc.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            const href = link ? link.getAttribute('href') : '';
            if (link && href !== '' && !href.startsWith('#') && !href.startsWith('javascript:')) {
                e.preventDefault();
                e.stopPropagation();
            }
            const button = e.target.closest('button[type="submit"], input[type="submit"], button:not([type])');
            if (button && button.form) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
        doc.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
        }, true);
    }

    let frame = null;
    let frameTarget = null;
    let pending = null;

    function load(url, onReady) {
        if (pending) {
            pending.remove();
        }
        const next = document.createElement('iframe');
        next.title = 'Prévia';
        next.className = 'is-loading';
        next.src = url;
        pending = next;
        next.addEventListener('load', function () {
            if (pending !== next) {
                return;
            }
            pending = null;
            if (onReady) {
                try {
                    onReady(next.contentDocument);
                } catch (e) {
                    // Cross-origin redirect (e.g. session expired): shown as is
                }
            }
            device.querySelectorAll('iframe').forEach(function (other) {
                if (other !== next) {
                    other.remove();
                }
            });
            next.classList.remove('is-loading');
            frame = next;
        });
        device.appendChild(next);
        fit();
    }

    function refresh() {
        if (!isOpen()) {
            return;
        }
        const current_target = target();
        if (note) {
            note.hidden = current_target === 'login';
        }
        // A new tab would show an internal page with the *saved* look only
        if (openLink) {
            openLink.hidden = current_target !== 'login';
        }

        if (current_target === 'login') {
            const url = previewUrl + '?' + buildParams();
            if (openLink) {
                openLink.href = url;
            }
            frameTarget = 'login';
            load(url, null);
            return;
        }

        // Same internal page already loaded: only swap its stylesheet
        if (frame && frameTarget === current_target && !pending && frame.contentDocument) {
            applyLiveCss(frame.contentDocument);
            return;
        }
        frameTarget = current_target;
        load(targetUrl(), function (doc) {
            makeInert(doc);
            applyLiveCss(doc);
        });
    }

    let timer = null;
    function scheduleRefresh() {
        setDirty(true);
        clearTimeout(timer);
        timer = setTimeout(refresh, 350);
    }

    function setPreview(open) {
        editor.classList.toggle('has-preview', open);
        editor.querySelectorAll('[data-preview-toggle][aria-expanded]').forEach(function (button) {
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        if (open) {
            refresh();
        } else {
            // Nothing keeps running (or loaded) while the preview is closed
            clearTimeout(timer);
            device.querySelectorAll('iframe').forEach(function (other) {
                other.remove();
            });
            frame = null;
            frameTarget = null;
            pending = null;
        }
    }

    editor.querySelectorAll('[data-preview-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            setPreview(!isOpen());
        });
    });

    if (targetSelect) {
        targetSelect.addEventListener('change', function () {
            frame = null;
            refresh();
        });
    }

    editor.querySelectorAll('[data-device]').forEach(function (button) {
        button.addEventListener('click', function () {
            current = button.dataset.device;
            editor.querySelectorAll('[data-device]').forEach(function (b) {
                b.classList.toggle('active', b === button);
            });
            fit();
        });
    });

    window.addEventListener('resize', fit);
    if (window.ResizeObserver) {
        new ResizeObserver(fit).observe(stage);
    }

    // ------------------------------------------------------------ form helpers

    function syncColor(input) {
        const wrapper = input.closest('.gs-color');
        const label = wrapper && wrapper.querySelector('[data-color-label]');
        if (label) {
            label.textContent = input.value;
        }
    }

    function syncDefault(checkbox) {
        checkbox.closest('.gs-color').classList.toggle('is-default', checkbox.checked);
    }

    form.addEventListener('input', function (e) {
        const target = e.target;
        if (target.type === 'range') {
            const output = target.closest('.gs-range').querySelector('output');
            output.textContent = target.value + (output.dataset.unit || '');
        }
        if (target.type === 'color') {
            syncColor(target);
            // Picking a color means leaving "Usar padrão do tema"
            const wrapper = target.closest('.gs-color--opt');
            const checkbox = wrapper && wrapper.querySelector('.gs-color__default input');
            if (checkbox && checkbox.checked) {
                checkbox.checked = false;
                syncDefault(checkbox);
            }
        }
        if (target.type !== 'file' && !target.matches('[data-preset]')) {
            scheduleRefresh();
        }
    });

    form.addEventListener('change', function (e) {
        const target = e.target;

        if (target.type === 'file') {
            const box = target.closest('[data-upload]');
            const file = target.files && target.files[0];
            if (box && file) {
                const preview = box.querySelector('.gs-upload__preview');
                preview.innerHTML = '';
                const img = document.createElement('img');
                img.alt = '';
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
                preview.classList.add('has-image');
                box.querySelector('.gs-upload__pending').hidden = false;
                setDirty(true);
            }
            return;
        }

        if (target.matches('[data-preset]')) {
            applyPreset(target.value);
            target.value = '';
            return;
        }

        if (target.matches('.gs-color__default input')) {
            syncDefault(target);
        }

        if (target.closest('.gs-position')) {
            const label = target.closest('.gs-position').querySelector('[data-position-label]');
            if (label) {
                label.textContent = target.dataset.label;
            }
        }

        if (target.tagName === 'SELECT') {
            const help = target.closest('.gs-control').querySelector('[data-select-help]');
            const option = target.selectedOptions[0];
            if (help && option) {
                help.textContent = option.dataset.help || '';
            }
        }

        if (target.type === 'checkbox' || target.type === 'radio' || target.tagName === 'SELECT') {
            scheduleRefresh();
        }
    });

    const presetSelect = form.querySelector('[data-preset]');
    if (presetSelect) {
        Object.keys(PRESETS).forEach(function (name) {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            presetSelect.appendChild(option);
        });
    }

    function applyPreset(name) {
        if (!(name in PRESETS)) {
            return;
        }
        const values = PRESETS[name];
        PRESET_FIELDS.forEach(function (field, i) {
            const input = form.querySelector('input[type="color"][name="' + field + '"]');
            const checkbox = form.querySelector('input[name="' + field + '__default"]');
            if (!input || !checkbox) {
                return;
            }
            if (values) {
                input.value = values[i];
                syncColor(input);
            }
            checkbox.checked = !values;
            syncDefault(checkbox);
        });
        scheduleRefresh();
    }

    form.addEventListener('submit', function (e) {
        const submitter = e.submitter;
        if (submitter && submitter.dataset.confirm && !window.confirm(submitter.dataset.confirm)) {
            e.preventDefault();
            return;
        }
        setDirty(false);
    });

    window.addEventListener('beforeunload', function (e) {
        if (dirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Remember which sections are collapsed
    form.querySelectorAll('details[data-section]').forEach(function (details) {
        if (store.get('section.' + details.dataset.section) === 'closed') {
            details.open = false;
        }
        details.addEventListener('toggle', function () {
            store.set('section.' + details.dataset.section, details.open ? 'open' : 'closed');
        });
    });

    // The preview always starts closed (see the preview section above)
})();
