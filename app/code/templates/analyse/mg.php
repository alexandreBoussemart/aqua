<?php
$sql_mg = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM ".TABLE_DATA_EAU." 
            WHERE `type` LIKE 'mg' 
            ORDER BY `id` DESC 
            LIMIT 9 
        ) T
        ORDER BY T.id ASC LIMIT 9";
$mg = mysqli_query($link, $sql_mg);
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Mg (mg/l)</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <div id="graph_mg" style="width:100%; height:300px;"></div>
        </div>
    </div>
</div>

<script>
<?php if(isset($mg) && $mg): ?>
    <?php
    $dateLast = $dateFirst = "";
    $isFirst = true;
    $eventsChangementdeauFinal = [];
    while ($obj = $mg->fetch_object()) {
        if ($isFirst) {
            $dateFirst = $obj->created_at;
            $isFirst = false;
        }
        $dateLastStr = $obj->created_at;
    }
    $dateLast = new DateTime($dateLastStr);
    $dateFirst = new DateTime($dateFirst);

    foreach ($changements as $changement) {
        $dateChangement = new DateTime($changement["created_at"]);
        if ($dateFirst <= $dateChangement && $dateChangement <= $dateLast) {
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
</script>