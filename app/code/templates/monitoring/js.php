<script>
    var URL_SAVE_ACTIONS_RAPIDES = '<?= $data['database'][0]['base_url'] ?>controller/saveActionsRapides'
</script>

<script>
    $(document).ready(function () {
        if ($(".progress .progress-bar")[0]) {
            $('.progress .progress-bar').progressbar();
        }
    });

    <?php if(isset($temperature_RPI) && $temperature_RPI): ?>
    if ($('#graph_temperature_rpi').length) {
        Morris.Line({
            element: 'graph_temperature_rpi',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Température raspberry'],
            yLabelFormat: function (y) {
                return y.toString() + ' °C';
            },
            goals: [40, 55],
            goalLineColors: ['#ffc107', '#dc3545'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 20,
            ymax: 80,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $temperature_RPI->fetch_object()){ ?>
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

</script>
