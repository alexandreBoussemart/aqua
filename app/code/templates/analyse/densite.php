<?php
$sql_densite = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM ".TABLE_DATA_EAU." 
            WHERE `type` LIKE 'densite' 
            ORDER BY `id` DESC 
            LIMIT 9 
        ) T
        ORDER BY T.id ASC LIMIT 9";
$densite = mysqli_query($link, $sql_densite);

?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Densité</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <div id="graph_densite" style="width:100%; height:300px;"></div>
        </div>
    </div>
</div>

<script>
<?php if(isset($densite) && $densite): ?>
    <?php
    $dateLast = $dateFirst = "";
    $isFirst = true;
    $eventsChangementdeauFinal = [];
    while ($obj = $densite->fetch_object()) {
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
</script>