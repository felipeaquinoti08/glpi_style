/*
 * GLPI Style - custom favicon. The core <link rel="shortcut icon"> is
 * printed after plugin header tags, so its href is swapped here with the
 * URL published in <meta name="glpistyle:favicon">.
 */
(function () {
    'use strict';

    function apply() {
        const meta = document.querySelector('meta[name="glpistyle:favicon"]');
        if (!meta || !meta.content) {
            return;
        }
        const links = document.querySelectorAll('link[rel~="icon"]');
        if (links.length === 0) {
            const link = document.createElement('link');
            link.rel = 'icon';
            document.head.appendChild(link);
            link.href = meta.content;
            return;
        }
        links.forEach(function (link) {
            link.removeAttribute('type');
            link.href = meta.content;
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', apply);
    } else {
        apply();
    }
})();
