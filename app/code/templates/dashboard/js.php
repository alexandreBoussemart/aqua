<script>
    var URL_SAVE_ACTIONS_RAPIDES = '<?= $data['database'][0]['base_url'] ?>controller/saveActionsRapides'
</script>

<script>

    <?php if(isset($temperature) && $temperature): ?>
    if ($('#graph_temperature_eau').length) {
        Morris.Line({
            element: 'graph_temperature_eau',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Température'],
            yLabelFormat: function (y) {
                return y.toString() + ' °C';
            },
            goals: [<?= getConfig($link, "temperature_min") ?>, 25, <?= getConfig($link, "config_temperature_declenchement") ?>, <?= getConfig($link, "temperature_max") ?>],
            goalLineColors: ['#2B46F0', '#7FFF00', '#ffc107', '#d43f3a'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 20,
            ymax: 30,
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

    <?php if(isset($temperature_air) && $temperature_air): ?>
    if ($('#graph_temperature_air').length) {
        Morris.Line({
            element: 'graph_temperature_air',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Température système'],
            yLabelFormat: function (y) {
                return y.toString() + ' °C';
            },
            goals: [15, 25],
            goalLineColors: ['#ffc107', '#dc3545'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 10,
            ymax: 30,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $temperature_air->fetch_object()){ ?>
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

</script>
