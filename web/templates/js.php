<script src="./build/js/custom.min.js"></script>
<script>
    var URL_SAVE_ACTIONS_RAPIDES = '<?= $data['database'][0]['base_url'] ?>ajax/saveActionsRapides.php'
    var URL_SAVE_STATUS = '<?= $data['database'][0]['base_url'] ?>ajax/saveStatus.php'
</script>

<script>
    <?php if(isset($temperature) && $temperature): ?>
    if ($('#graph_temperature').length) {
        Morris.Line({
            element: 'graph_temperature',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Température'],
            yLabelFormat: function (y) {
                return y.toString() + ' °C';
            },
            goals: [<?= getConfig($link, "temperature_min") ?>, 25, <?= getConfig($link, "temperature_max") ?>],
            goalLineColors: ['#2B46F0', '#7FFF00', '#d43f3a'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 15,
            ymax: 35,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $temperature->fetch_object()){ ?>
                {
                    datetime: '<?= $obj->created_at; ?>',
                    value: <?= $obj->value; ?>,
                    formatted_datetime: '<?= getFormattedDate($obj->created_at); ?>'
                },
                <?php } ?>
            ],
            resize: true,
            hoverCallback: function (index, options, content, row) {
                content = content.replace(row.datetime, row.formatted_datetime);
                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($reacteur) && $reacteur): ?>
    if ($('#graph_debit').length) {
        Morris.Line({
            element: 'graph_debit',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Débit'],
            yLabelFormat: function (y) {
                return y.toString();
            },
            xLabelFormat: function (d) {
                return ("0" + (d.getHours())).slice(-2) + ':' +
                    ("0" + (d.getMinutes())).slice(-2);
            },
            goals: [<?= getConfig($link, "debit_reacteur_min") ?>],
            goalLineColors: ['#2B46F0'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 900,
            ymax: 1500,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $reacteur->fetch_object()){ ?>
                {
                    datetime: '<?= $obj->created_at; ?>',
                    value: <?= $obj->value; ?>,
                    formatted_datetime: '<?= getFormattedDate($obj->created_at); ?>'
                },
                <?php } ?>
            ],
            resize: true,
            hoverCallback: function (index, options, content, row) {
                content = content.replace(row.datetime, row.formatted_datetime);
                content = content.replace(row.value, row.value + ' l/h');

                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($kh) && $kh): ?>
    if ($('#graph_kh').length) {
        Morris.Line({
            element: 'graph_kh',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Kh'],
            yLabelFormat: function (y) {
                return y.toString() + ' dkh';
            },
            xLabelFormat: function (d) {
                return ("0" + (d.getDate())).slice(-2) + '/' +
                    ("0" + (d.getMonth() + 1)).slice(-2);
            },
            goals: [6, 7, 10],
            goalLineColors: ['#2B46F0', '#7FFF00', '#d43f3a'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 4,
            ymax: 12,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $kh->fetch_object()){ ?>
                {
                    datetime: '<?= $obj->created_at; ?>',
                    value: <?= $obj->value; ?>,
                    formatted_datetime: '<?= getFormattedDateWithouH($obj->created_at); ?>'
                },
                <?php } ?>
            ],
            resize: true,
            hoverCallback: function (index, options, content, row) {
                content = content.replace(row.datetime, row.formatted_datetime);
                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($ca) && $ca): ?>
    if ($('#graph_ca').length) {
        Morris.Line({
            element: 'graph_ca',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Ca'],
            yLabelFormat: function (y) {
                return y.toString()
            },
            xLabelFormat: function (d) {
                return ("0" + (d.getDate())).slice(-2) + '/' +
                    ("0" + (d.getMonth() + 1)).slice(-2);
            },
            goals: [400, 420, 450],
            goalLineColors: ['#2B46F0', '#7FFF00', '#d43f3a'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 350,
            ymax: 500,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $ca->fetch_object()){ ?>
                {
                    datetime: '<?= $obj->created_at; ?>',
                    value: <?= $obj->value; ?>,
                    formatted_datetime: '<?= getFormattedDateWithouH($obj->created_at); ?>'
                },
                <?php } ?>
            ],
            resize: true,
            hoverCallback: function (index, options, content, row) {
                content = content.replace(row.datetime, row.formatted_datetime);
                content = content.replace(row.value, row.value + ' mg/l');

                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($mg) && $mg): ?>
    if ($('#graph_mg').length) {
        Morris.Line({
            element: 'graph_mg',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Mg'],
            yLabelFormat: function (y) {
                return y.toString();
            },
            xLabelFormat: function (d) {
                return ("0" + (d.getDate())).slice(-2) + '/' +
                    ("0" + (d.getMonth() + 1)).slice(-2);
            },
            goals: [1150, 1300, 1400],
            goalLineColors: ['#2B46F0', '#7FFF00', '#d43f3a'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 1100,
            ymax: 1600,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $mg->fetch_object()){ ?>
                {
                    datetime: '<?= $obj->created_at; ?>',
                    value: <?= $obj->value; ?>,
                    formatted_datetime: '<?= getFormattedDateWithouH($obj->created_at); ?>'
                },
                <?php } ?>
            ],
            resize: true,
            hoverCallback: function (index, options, content, row) {
                content = content.replace(row.datetime, row.formatted_datetime);
                content = content.replace(row.value, row.value + ' mg/l');

                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($densite) && $densite): ?>
    if ($('#graph_densite').length) {
        Morris.Line({
            element: 'graph_densite',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Densité'],
            yLabelFormat: function (y) {
                return y.toString();
            },
            xLabelFormat: function (d) {
                return ("0" + (d.getDate())).slice(-2) + '/' +
                    ("0" + (d.getMonth() + 1)).slice(-2);
            },
            goals: [1024, 1025, 1027],
            goalLineColors: ['#2B46F0', '#7FFF00', '#d43f3a'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 1020,
            ymax: 1028,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $densite->fetch_object()){ ?>
                {
                    datetime: '<?= $obj->created_at; ?>',
                    value: <?= $obj->value; ?>,
                    formatted_datetime: '<?= getFormattedDateWithouH($obj->created_at); ?>'
                },
                <?php } ?>
            ],
            resize: true,
            hoverCallback: function (index, options, content, row) {
                content = content.replace(row.datetime, row.formatted_datetime);
                return (content);
            }
        });
    }
    <?php endif; ?>

    $MENU_TOGGLE.on('click', function () {
        $(window).resize();
    });

    <?php if(isset($logs)): ?>
    $('#datatable').dataTable({'order': [[0, 'desc']]});
    <?php endif; ?>
    <?php if(isset($changements)): ?>
    $('#datatable-eau').dataTable({'order': [[0, 'desc']]});
    <?php endif; ?>
    <?php if(isset($logs_mails)): ?>
    $('#datatable-mail').dataTable({'order': [[0, 'desc']]});
    <?php endif; ?>

</script>
