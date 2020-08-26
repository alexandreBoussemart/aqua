<script src="./build/js/custom.min.js"></script>
<script>
    var URL_SAVE_ACTIONS_RAPIDES = '<?= $data['database'][0]['base_url'] ?>controller/saveActionsRapides'
    var URL_SAVE_STATUS = '<?= $data['database'][0]['base_url'] ?>controller/saveStatus'
</script>

<script>
    <?php
    // changement eau
    $sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT * 
        FROM `data_changement_eau` 
        LIMIT 50;";
    $request = mysqli_query($link, $sql);
    $changements = mysqli_query($link, $sql);
    $eventsChangementdeau = [];
    ?>

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
            goals: [40, <?= getConfig($link, "temperature_max_rpi") ?>],
            goalLineColors: ['#ffc107', '#dc3545'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 30,
            ymax: 90,
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

    <?php if(isset($temperature_systeme) && $temperature_systeme): ?>
    if ($('#graph_temperature_boitier').length) {
        Morris.Line({
            element: 'graph_temperature_boitier',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Température système'],
            yLabelFormat: function (y) {
                return y.toString() + ' °C';
            },
            goals: [<?= getConfig($link, "temperature_max_boitier") ?>, 40],
            goalLineColors: ['#ffc107', '#dc3545'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 25,
            ymax: 45,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $temperature_systeme->fetch_object()){ ?>
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
    <?php
    $dateLast = $dateFirst = "";
    $isFirst = true;
    $eventsChangementdeauFinal = [];
    while ($obj = $kh->fetch_object()) {
        if($isFirst){
            $dateFirst = $obj->created_at;
            $isFirst = false;
        }
        $dateLastStr = $obj->created_at;
    }
    $dateLast = new DateTime($dateLastStr);
    $dateFirst = new DateTime($dateFirst);
    foreach ($changements as $changement) {
        $dateChangement = new DateTime($changement["created_at"]);
        if($dateFirst <= $dateChangement && $dateLast >= $dateChangement ) {
            $eventsChangementdeauFinal[] = "'" . $changement["created_at"] . "'";
        }
    }
    $eventsChangementdeauFinal = "[" . implode(",", $eventsChangementdeauFinal) . "]";
    $kh = mysqli_query($link, $sql_kh);
    ?>

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
            events: <?= $eventsChangementdeauFinal ?>,
            eventLineColors: ['#007bff'],
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
    <?php
    $dateLast = $dateFirst = "";
    $isFirst = true;
    $eventsChangementdeauFinal = [];
    while ($obj = $ca->fetch_object()) {
        if($isFirst){
            $dateFirst = $obj->created_at;
            $isFirst = false;
        }
        $dateLastStr = $obj->created_at;
    }
    $dateLast = new DateTime($dateLastStr);
    $dateFirst = new DateTime($dateFirst);
    foreach ($changements as $changement) {
        $dateChangement = new DateTime($changement["created_at"]);
        if($dateFirst <= $dateChangement && $dateLast >= $dateChangement ) {
            $eventsChangementdeauFinal[] = "'" . $changement["created_at"] . "'";
        }
    }
    $eventsChangementdeauFinal = "[" . implode(",", $eventsChangementdeauFinal) . "]";
    $ca = mysqli_query($link, $sql_ca);
    ?>

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
            events: <?= $eventsChangementdeauFinal ?>,
            eventLineColors: ['#007bff'],
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
    <?php
    $dateLast = $dateFirst = "";
    $isFirst = true;
    $eventsChangementdeauFinal = [];
    while ($obj = $mg->fetch_object()) {
        if($isFirst){
            $dateFirst = $obj->created_at;
            $isFirst = false;
        }
        $dateLastStr = $obj->created_at;
    }
    $dateLast = new DateTime($dateLastStr);
    $dateFirst = new DateTime($dateFirst);

    foreach ($changements as $changement) {
        $dateChangement = new DateTime($changement["created_at"]);
        if($dateFirst <= $dateChangement && $dateChangement <= $dateLast ) {
            $eventsChangementdeauFinal[] = "'" . $changement["created_at"] . "'";
        }
    }
    $eventsChangementdeauFinal = "[" . implode(",", $eventsChangementdeauFinal) . "]";
    $mg = mysqli_query($link, $sql_mg);
    ?>

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
            events: <?= $eventsChangementdeauFinal ?>,
            eventLineColors: ['#007bff'],
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
    <?php
    $dateLast = $dateFirst = "";
    $isFirst = true;
    $eventsChangementdeauFinal = [];
    while ($obj = $densite->fetch_object()) {
        if($isFirst){
            $dateFirst = $obj->created_at;
            $isFirst = false;
        }
        $dateLastStr = $obj->created_at;
    }
    $dateLast = new DateTime($dateLastStr);
    $dateFirst = new DateTime($dateFirst);

    foreach ($changements as $changement) {
        $dateChangement = new DateTime($changement["created_at"]);
        if($dateFirst <= $dateChangement && $dateChangement <= $dateLast ) {
            $eventsChangementdeauFinal[] = "'" . $changement["created_at"] . "'";
        }
    }
    $eventsChangementdeauFinal = "[" . implode(",", $eventsChangementdeauFinal) . "]";
    $densite = mysqli_query($link, $sql_densite);
    ?>
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
            events: <?= $eventsChangementdeauFinal ?>,
            eventLineColors: ['#007bff'],
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
    <?php if(isset($budget)): ?>
    $('#datatable_budget').dataTable({'order': [[0, 'desc']]});
    <?php endif; ?>

</script>
