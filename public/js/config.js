/*
 * GLPI Style - config page: live preview and small form helpers.
 *
 * The preview iframe renders front/preview.php with the unsaved values
 * in the query string. A new iframe is loaded in the background and
 * swapped in once ready, so editing never flashes a blank frame.
 */
(function () {
    'use strict';

    const editor = document.getElementById('gs-editor');
    const form = document.getElementById('gs-editor-form');
    const stage = document.getElementById('gs-preview-stage');
    const device = document.getElementById('gs-preview-device');
    const openLink = document.getElementById('gs-preview-open');
    if (!editor || !form || !stage || !device) {
        return;
    }

    const previewUrl = editor.dataset.previewUrl;
    const devices = {
        desktop: [1440, 900],
        tablet: [834, 1112],
        mobile: [390, 844],
    };
    let current = 'desktop';
    let dirty = false;

    function buildUrl() {
        const params = new URLSearchParams();
        params.set('_live', '1');
        new FormData(form).forEach(function (value, name) {
            if (value instanceof File || name.startsWith('remove_') || name.startsWith('_glpi_')) {
                return;
            }
            params.append(name, value);
        });
        return previewUrl + '?' + params.toString();
    }

    function fit() {
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

    let pending = null;
    function refresh() {
        const url = buildUrl();
        if (openLink) {
            openLink.href = url;
        }
        if (pending) {
            pending.remove();
        }
        const next = document.createElement('iframe');
        next.title = 'Prévia da tela de login';
        next.className = 'is-loading';
        next.src = url;
        pending = next;
        next.addEventListener('load', function () {
            if (pending !== next) {
                return;
            }
            pending = null;
            device.querySelectorAll('iframe').forEach(function (frame) {
                if (frame !== next) {
                    frame.remove();
                }
            });
            next.classList.remove('is-loading');
        });
        device.appendChild(next);
        fit();
    }

    let timer = null;
    function scheduleRefresh() {
        dirty = true;
        clearTimeout(timer);
        timer = setTimeout(refresh, 350);
    }

    form.addEventListener('input', function (e) {
        const target = e.target;
        if (target.type === 'range') {
            const output = target.closest('.gs-range').querySelector('output');
            output.textContent = target.value + (output.dataset.unit || '');
        }
        if (target.type === 'color') {
            const label = target.closest('.gs-color').querySelector('[data-color-label]');
            label.textContent = target.value;
        }
        if (target.type !== 'file') {
            scheduleRefresh();
        }
    });
    form.addEventListener('change', function (e) {
        const target = e.target;
        if (target.type === 'file') {
            const box = target.closest('[data-upload]');
            const file = target.files && target.files[0];
            if (box && file) {
                const thumb = box.querySelector('.gs-upload__thumb');
                thumb.innerHTML = '';
                const img = document.createElement('img');
                img.alt = '';
                img.src = URL.createObjectURL(file);
                thumb.appendChild(img);
                box.classList.add('has-image');
                box.querySelector('.gs-upload__pending').hidden = false;
                dirty = true;
            }
            return;
        }
        if (target.type === 'checkbox' || target.type === 'radio' || target.tagName === 'SELECT') {
            scheduleRefresh();
        }
    });

    form.addEventListener('submit', function (e) {
        const submitter = e.submitter;
        if (submitter && submitter.dataset.confirm && !window.confirm(submitter.dataset.confirm)) {
            e.preventDefault();
            return;
        }
        dirty = false;
    });

    window.addEventListener('beforeunload', function (e) {
        if (dirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

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
    fit();
})();
