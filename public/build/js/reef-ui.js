/*
 * Bulles d'aide « i » (templates/_partials/info.html.twig).
 * La bulle est rattachée au <body> (au-dessus de la barre du haut et des panneaux) puis placée
 * au-dessus de l'icône s'il y a la place, sinon en dessous, et recalée dans la largeur de l'écran.
 */
(function () {
    'use strict';
    var TOP_BAR = 64, GAP = 12, EDGE = 10, current = null;

    function place(icon, pop) {
        var r = icon.getBoundingClientRect(), w = pop.offsetWidth, h = pop.offsetHeight;
        var vw = document.documentElement.clientWidth, vh = document.documentElement.clientHeight;
        var cx = r.left + r.width / 2;

        var below = r.top - GAP - h < TOP_BAR && r.bottom + GAP + h <= vh;
        var top = below ? r.bottom + GAP : r.top - GAP - h;
        var left = Math.min(Math.max(cx - 28, EDGE), vw - w - EDGE);

        pop.style.top = Math.round(top) + 'px';
        pop.style.left = Math.round(left) + 'px';
        pop.style.setProperty('--arrow-x', Math.round(Math.min(Math.max(cx - left, 18), w - 18)) + 'px');
        pop.classList.toggle('is-below', below);
    }

    function open(icon) {
        if (current && current.icon !== icon) {
            close();
        }
        var pop = icon._reefPop;
        if (!pop) {
            pop = icon.querySelector('.reef-info-pop');
            if (!pop) {
                return;
            }
            icon._reefPop = pop;
            document.body.appendChild(pop);
        }
        place(icon, pop);
        pop.classList.add('is-open');
        icon.setAttribute('aria-expanded', 'true');
        current = {icon: icon, pop: pop};
    }

    function close() {
        if (!current) {
            return;
        }
        current.pop.classList.remove('is-open');
        current.icon.setAttribute('aria-expanded', 'false');
        current = null;
    }

    document.addEventListener('mouseover', function (e) {
        var icon = e.target.closest && e.target.closest('.reef-info');
        if (icon) { open(icon); }
    });
    document.addEventListener('mouseout', function (e) {
        var icon = e.target.closest && e.target.closest('.reef-info');
        if (icon && !icon.contains(e.relatedTarget)) { close(); }
    });
    document.addEventListener('focusin', function (e) {
        if (e.target.classList && e.target.classList.contains('reef-info')) { open(e.target); }
    });
    document.addEventListener('focusout', function (e) {
        if (e.target.classList && e.target.classList.contains('reef-info')) { close(); }
    });
    // mobile : un toucher ouvre / ferme, un toucher ailleurs ferme
    document.addEventListener('click', function (e) {
        var icon = e.target.closest && e.target.closest('.reef-info');
        if (icon) {
            e.preventDefault();
            current && current.icon === icon ? close() : open(icon);
        } else {
            close();
        }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { close(); }
    });
    window.addEventListener('scroll', close, true);
    window.addEventListener('resize', close);

    // loader « poisson-clown » pendant l'envoi d'un formulaire (les AJAX l'affichent via showLoader() de custom.js)
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (e.defaultPrevented || form.hasAttribute('data-no-loader') || (form.getAttribute('action') || '').indexOf('javascript:') === 0) {
            return;
        }
        var mask = document.querySelector('.loading-mask');
        if (mask) {
            mask.style.display = 'flex';
        }
    });
    // retour arrière (cache navigateur) : ne pas laisser le loader affiché
    window.addEventListener('pageshow', function () {
        var mask = document.querySelector('.loading-mask');
        if (mask) {
            mask.style.display = '';
        }
    });
})();
