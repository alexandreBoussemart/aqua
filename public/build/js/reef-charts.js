/*
 * Graphiques « Récif » (Chart.js 4 + chartjs-plugin-annotation).
 * Config produite par App\Service\ChartBuilder : points {x (ms), y, d (date affichée), s (ok|above|below)},
 * limites {min, max, ideal, warn}, échelle suggérée (élargie automatiquement si une valeur dépasse).
 */
(function () {
    'use strict';
    if (!window.Chart) {
        return;
    }

    var C = {
        lagoon: '#0096C7', ocean: '#0B5C80', coral: '#FF6F61', below: '#1D4E89',
        seaweed: '#2A9D8F', anemone: '#F77F00', muted: '#5B7C93',
        zone: 'rgba(42, 157, 143, 0.10)', grid: 'rgba(72, 202, 228, 0.18)'
    };

    Chart.defaults.font.family = 'Nunito, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = C.muted;

    function fmt(v) {
        return (Math.round(v * 100) / 100).toLocaleString('fr-FR');
    }

    function stateColor(s) {
        return s === 'above' ? C.coral : (s === 'below' ? C.below : C.lagoon);
    }

    function limitLine(value, color, text, dash, unit) {
        return {
            type: 'line', yMin: value, yMax: value, borderColor: color, borderWidth: 1.5, borderDash: dash,
            label: {
                display: true, content: text + ' ' + fmt(value) + (unit ? ' ' + unit : ''), position: 'start',
                backgroundColor: color, color: '#fff', font: {size: 10, weight: 'bold'}, padding: {x: 6, y: 2}, borderRadius: 999
            }
        };
    }

    function pad(v) {
        return ('0' + v).slice(-2);
    }

    window.ReefChart = {
        render: function (cfg) {
            var canvas = document.getElementById(cfg.id);
            if (!canvas) {
                return;
            }
            if (!cfg.points.length) {
                canvas.parentNode.innerHTML = '<p class="reef-chart-empty"><i class="fa fa-info-circle"></i> Aucune mesure sur la période</p>';
                return;
            }

            var L = cfg.limits, points = cfg.points, unit = cfg.unit, annotations = {};

            // zone de confort entre min et max (ou sous le max seul)
            if (L.max !== null) {
                annotations.zone = {type: 'box', yMax: L.max, backgroundColor: C.zone, borderWidth: 0, drawTime: 'beforeDatasetsDraw'};
                if (L.min !== null) {
                    annotations.zone.yMin = L.min;
                }
            }
            if (L.max !== null) { annotations.max = limitLine(L.max, C.coral, 'max', [6, 4], unit); }
            if (L.min !== null) { annotations.min = limitLine(L.min, C.below, 'min', [6, 4], unit); }
            if (L.warn !== null) { annotations.warn = limitLine(L.warn, C.anemone, 'vigilance', [4, 4], unit); }
            if (L.ideal !== null) { annotations.ideal = limitLine(L.ideal, C.seaweed, 'idéal', [2, 4], unit); }
            // changements d'eau : trait vertical (étiquette seulement s'ils sont peu nombreux)
            var events = cfg.events || [], labelEvents = events.length <= 3;
            events.forEach(function (x, i) {
                annotations['event' + i] = {
                    type: 'line', xMin: x, xMax: x, borderColor: 'rgba(0, 150, 199, 0.55)', borderWidth: 1, borderDash: [3, 3],
                    label: {display: labelEvents, content: '\u21bb eau', position: 'start', backgroundColor: 'rgba(0, 150, 199, 0.85)', font: {size: 9}, padding: 3, borderRadius: 6}
                };
            });

            var dense = points.length > 30;

            // bornes de l'axe Y : échelle suggérée élargie aux valeurs réelles (+5 %), jamais négative pour des grandeurs positives
            var ys = points.map(function (p) { return p.y; });
            var lo = Math.min(cfg.suggestedMin, Math.min.apply(null, ys)), hi = Math.max(cfg.suggestedMax, Math.max.apply(null, ys));
            var margin = (hi - lo) * 0.05 || 1;
            // pas « rond » (1, 2, 2,5, 5 × 10^n) pour des graduations lisibles
            var raw = (hi - lo + 2 * margin) / 6, mag = Math.pow(10, Math.floor(Math.log10(raw))), norm = raw / mag;
            var step = (norm <= 1 ? 1 : norm <= 2 ? 2 : norm <= 2.5 ? 2.5 : norm <= 5 ? 5 : 10) * mag;
            var yMin = Math.floor((lo - margin) / step) * step, yMax = Math.ceil((hi + margin) / step) * step;
            if (yMin < 0 && cfg.suggestedMin >= 0 && Math.min.apply(null, ys) >= 0) {
                yMin = 0;
            }
            // période > 10 mois : l'année est ajoutée aux dates de l'axe
            // l'axe X va jusqu'au dernier point ou au dernier changement d'eau s'il est plus récent
            var xMax = Math.max(points[points.length - 1].x, events.length ? events[events.length - 1] : 0);
            var withYear = xMax - points[0].x > 300 * 86400000;
            new Chart(canvas, {
                type: 'line',
                data: {
                    datasets: [{
                        label: cfg.label,
                        data: points,
                        parsing: false,
                        borderWidth: 2.5,
                        tension: 0.3,
                        cubicInterpolationMode: 'monotone', // pas de dépassement fictif entre deux mesures
                        borderColor: C.lagoon,
                        fill: 'start',
                        backgroundColor: function (ctx) {
                            var area = ctx.chart.chartArea;
                            if (!area) { return 'rgba(0, 150, 199, 0.12)'; }
                            var g = ctx.chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
                            g.addColorStop(0, 'rgba(0, 150, 199, 0.22)');
                            g.addColorStop(1, 'rgba(0, 150, 199, 0)');
                            return g;
                        },
                        segment: {
                            borderColor: function (ctx) {
                                var a = points[ctx.p0DataIndex].s, b = points[ctx.p1DataIndex].s;
                                return a !== 'ok' ? stateColor(a) : (b !== 'ok' ? stateColor(b) : undefined);
                            }
                        },
                        pointBackgroundColor: function (ctx) { return stateColor(ctx.raw && ctx.raw.s); },
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        pointRadius: function (ctx) { return ctx.raw && ctx.raw.s !== 'ok' ? 5 : (dense ? 0 : 3.5); },
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {duration: 600},
                    interaction: {mode: 'nearest', axis: 'x', intersect: false},
                    layout: {padding: {top: 6, right: 18}},
                    scales: {
                        x: {
                            type: 'linear',
                            min: points[0].x,
                            max: xMax,
                            grid: {color: C.grid},
                            ticks: {
                                maxTicksLimit: cfg.dateOnly ? 6 : 8,
                                callback: function (v) {
                                    var d = new Date(v);
                                    if (!cfg.dateOnly) {
                                        return pad(d.getHours()) + 'h' + pad(d.getMinutes());
                                    }
                                    return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + (withYear ? '/' + String(d.getFullYear()).slice(2) : '');
                                }
                            }
                        },
                        y: {
                            min: yMin,
                            max: yMax,
                            ticks: {stepSize: step, callback: function (v) { return fmt(v) + (unit ? ' ' + unit : ''); }},
                            grid: {color: C.grid}
                        }
                    },
                    plugins: {
                        legend: {display: false},
                        annotation: {annotations: annotations},
                        tooltip: {
                            backgroundColor: '#063A5B',
                            padding: 10,
                            cornerRadius: 10,
                            displayColors: false,
                            callbacks: {
                                title: function (items) { return items[0].raw.d; },
                                label: function (item) {
                                    var s = item.raw.s, txt = cfg.label + ' : ' + fmt(item.raw.y) + (unit ? ' ' + unit : '');
                                    return s === 'above' ? [txt, '▲ au-dessus du maximum'] : (s === 'below' ? [txt, '▼ sous le minimum'] : txt);
                                }
                            }
                        }
                    }
                }
            });
        }
    };
})();
