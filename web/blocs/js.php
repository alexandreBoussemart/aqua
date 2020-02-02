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

<script>
    if ($('#graph_temperature').length) {
        Morris.Line({
            element: 'graph_temperature',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Value'],
            goals: [23, 25, 28],
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
                {datetime: '<?= $obj->created_at; ?>', value: <?= $obj->value; ?>},
                <?php } ?>
            ],
            resize: true
        });
    }

    if ($('#graph_debit').length) {
        Morris.Line({
            element: 'graph_debit',
            xkey: 'datetime',
            ykeys: ['value'],
            labels: ['Value'],
            goals: [1200],
            goalLineColors: ['#2B46F0'],
            goalStrokeWidth: '2',
            pointStrokeColors: ['#2A3F54'],
            hideHover: 'auto',
            ymin: 1100,
            ymax: 1500,
            pointSize: 1,
            lineColors: ['#2A3F54'],
            data: [
                <?php while($obj = $reacteur->fetch_object()){ ?>
                {datetime: '<?= $obj->created_at; ?>', value: <?= $obj->value; ?>},
                <?php } ?>
            ],
            resize: true
        });
    }

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
                {datetime: '2020-02-01 22:25:23', value: 8},
                {datetime: '2020-01-25 22:25:23', value: 7.7}
            ],
            resize: true
        });
    }

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
                {datetime: '2020-02-01 22:25:23', value: 425},
                {datetime: '2020-01-25 22:25:23', value: 429}
            ],
            resize: true
        });
    }

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
                {datetime: '2020-02-01 22:25:23', value: 1455},
                {datetime: '2020-01-25 22:25:23', value: 1400}
            ],
            resize: true
        });
    }

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
                {datetime: '2020-02-01 22:25:23', value: 1023},
                {datetime: '2020-01-25 22:25:23', value: 1024}
            ],
            resize: true
        });
    }

    $MENU_TOGGLE.on('click', function () {
        $(window).resize();
    });

    $('#datatable').dataTable({'order': [[ 0, 'desc' ]]});
    $('#datatable-eau').dataTable({'order': [[ 0, 'desc' ]]});

</script>
