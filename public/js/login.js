/*
 * GLPI Style - premium login page.
 *
 * mount() is called by an inline <script> printed right after the
 * DISPLAY_LOGIN hook markup (GlpiPlugin\Glpistyle\Login::display()),
 * while the page is still being parsed: the whole login form already
 * exists at that point, so the layout is applied before the first paint.
 */
(function () {
    'use strict';

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (text) {
            node.textContent = text;
        }
        return node;
    }

    function wrapField(input, icon, withToggle) {
        if (!input || input.parentElement.classList.contains('gs-field')) {
            return;
        }
        const wrapper = el('div', 'gs-field');
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const iconEl = el('i', 'ti ti-' + icon + ' gs-field__icon');
        iconEl.setAttribute('aria-hidden', 'true');
        wrapper.insertBefore(iconEl, input);

        if (withToggle) {
            wrapper.classList.add('gs-field--toggle');
            const toggle = el('button', 'gs-toggle-password');
            toggle.type = 'button';
            toggle.setAttribute('aria-label', 'Mostrar senha');
            toggle.setAttribute('aria-pressed', 'false');
            const eye = el('i', 'ti ti-eye');
            eye.setAttribute('aria-hidden', 'true');
            toggle.appendChild(eye);
            toggle.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                eye.className = 'ti ' + (show ? 'ti-eye-off' : 'ti-eye');
                toggle.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
                toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
                input.focus();
            });
            wrapper.appendChild(toggle);
        }
    }

    function mount() {
        const hero = document.getElementById('gs-hero');
        const configEl = document.getElementById('gs-login-config');
        const main = document.querySelector('main.page-anonymous');
        if (!hero || !configEl || !main || hero.dataset.mounted) {
            return;
        }
        hero.dataset.mounted = '1';

        let config;
        try {
            config = JSON.parse(configEl.textContent);
        } catch (e) {
            return;
        }

        const sideColumn = hero.parentElement;
        const body = document.body;
        body.insertBefore(hero, main);
        const isPanel = config.position.indexOf('panel_') === 0;
        body.classList.add(
            'gs-login',
            'gs-pos-' + config.position,
            isPanel ? 'gs-mode-panel' : 'gs-mode-card',
            'gs-form-' + config.formTheme,
            'gs-logo-' + config.logoPosition,
            'gs-align-' + config.textAlign,
            'gs-btn-' + config.buttonStyle,
            'gs-footer-' + config.footerMode
        );
        if (config.titleDivider) {
            body.classList.add('gs-divider');
        }
        if (config.glass) {
            body.classList.add('gs-glass');
        }
        if (config.heroInCard) {
            body.classList.add('gs-hero-in-card');
        }
        if (config.heroBackdrop === 'glass') {
            body.classList.add('gs-hero-glass');
        }
        if (config.animated) {
            body.classList.add('gs-animated');
        }
        if (config.preview) {
            body.classList.add('gs-preview');
        }

        const form = main.querySelector('form');
        const formColumn = form ? form.querySelector('.row > .col-md-5') : null;

        if (formColumn) {
            const title = formColumn.querySelector('.card-header h2');
            if (title && config.formTitle) {
                title.textContent = config.formTitle;
            }
            if (title && config.formSubtitle) {
                title.insertAdjacentElement('afterend', el('p', 'gs-form-subtitle', config.formSubtitle));
            }

            const login = document.getElementById('login_name');
            const password = document.getElementById('login_password');
            wrapField(login, 'user', false);
            wrapField(password, 'lock', config.passwordToggle);
            if (login) {
                login.setAttribute('autocomplete', 'username');
            }

            const submit = formColumn.querySelector('button[type="submit"]');
            if (submit) {
                if (config.buttonText) {
                    submit.textContent = config.buttonText;
                }
                const arrow = el('i', 'ti ti-arrow-right gs-btn-arrow');
                arrow.setAttribute('aria-hidden', 'true');
                submit.appendChild(arrow);
            }
        }

        // The core side column now only holds other plugins' output
        // (e.g. an SSO button) - show it under the form with a separator.
        if (sideColumn && sideColumn.classList.contains('col-auto')) {
            const hasContent = Array.from(sideColumn.children).some(function (child) {
                return !['SCRIPT', 'STYLE'].includes(child.tagName);
            });
            if (hasContent) {
                sideColumn.classList.add('gs-extra');
                if (config.extraSeparator) {
                    sideColumn.insertBefore(el('div', 'gs-separator', config.extraSeparator), sideColumn.firstChild);
                }
            } else {
                sideColumn.classList.add('gs-empty');
            }
        }

        const container = main.querySelector('.container-tight');
        if (container) {
            // The core copyright block is printed *after* the form, so it
            // doesn't exist yet at this point (mount() runs mid-parse): it
            // is hidden through the gs-footer-* body class in login.css.
            // Appended now, the custom text lands right before it.
            if (config.footerText && (config.footerMode === 'custom' || config.footerMode === 'both')) {
                container.appendChild(el('div', 'gs-footer', config.footerText));
            }
        }

        if (config.preview && form) {
            // Inert preview inside the config page iframe
            form.addEventListener('submit', function (e) {
                e.preventDefault();
            });
            document.addEventListener('click', function (e) {
                if (e.target.closest('a')) {
                    e.preventDefault();
                }
            }, true);
        }
    }

    window.GlpiStyleLogin = { mount: mount };
})();
