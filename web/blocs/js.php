<script src="./vendors/jquery/dist/jquery.min.js"></script>
<script src="./vendors/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="./build/js/custom.min.js"></script>
<script src="./vendors/raphael/raphael.min.js"></script>
<script src="./vendors/morris.js/morris.min.js"></script>
<script src="./vendors/switchery/dist/switchery.min.js"></script>

<!-- Datatables -->
<script src="./vendors/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="./vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
<script src="./vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="./vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
<script src="./vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
<script src="./vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="./vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="./vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
<script src="./vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
<script src="./vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="./vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
<script src="./vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
<script src="./vendors/jszip/dist/jszip.min.js"></script>
<script src="./vendors/pdfmake/build/pdfmake.min.js"></script>
<script src="./vendors/pdfmake/build/vfs_fonts.js"></script>

<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM `data_parametres_eau` 
            WHERE `type` LIKE 'ca' 
            ORDER BY `id` DESC 
            LIMIT 15 
        ) T
        ORDER BY T.id ASC LIMIT 15 ";
$ca = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM `data_parametres_eau` 
            WHERE `type` LIKE 'kh' 
            ORDER BY `id` DESC 
            LIMIT 15 
        ) T
        ORDER BY T.id ASC LIMIT 15";
$kh = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM `data_parametres_eau` 
            WHERE `type` LIKE 'mg' 
            ORDER BY `id` DESC 
            LIMIT 15 
        ) T
        ORDER BY T.id ASC LIMIT 15";
$mg = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM `data_parametres_eau` 
            WHERE `type` LIKE 'densite' 
            ORDER BY `id` DESC 
            LIMIT 15 
        ) T
        ORDER BY T.id ASC LIMIT 15";
$densite = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `data_temperature` 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$temperature = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `data_reacteur` 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$reacteur = mysqli_query($link, $sql);

?>

<script>
    <?php if(isset($temperature)): ?>
    if ($('#graph_temperature').length) {
        Morris.Line({
            element: 'graph_temperature',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Value'],
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

    <?php if(isset($reacteur)): ?>
    if ($('#graph_debit').length) {
        Morris.Line({
            element: 'graph_debit',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Value'],
            goals: [1000],
            goalLineColors: ['#2B46F0'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 800,
            ymax: 1300,
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
                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($kh)): ?>
    if ($('#graph_kh').length) {
        Morris.Line({
            element: 'graph_kh',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Taux Kh'],
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

    <?php if(isset($ca)): ?>
    if ($('#graph_ca').length) {
        Morris.Line({
            element: 'graph_ca',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Taux Ca'],
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
                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($mg)): ?>
    if ($('#graph_mg').length) {
        Morris.Line({
            element: 'graph_mg',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Taux Mg'],
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
                return (content);
            }
        });
    }
    <?php endif; ?>

    <?php if(isset($densite)): ?>
    if ($('#graph_densite').length) {
        Morris.Line({
            element: 'graph_densite',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Densité'],
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
