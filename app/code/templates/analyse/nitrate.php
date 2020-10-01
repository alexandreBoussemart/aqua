<?php
$sql_nitrate = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM `data_parametres_eau` 
            WHERE `type` LIKE 'nitrate' 
            ORDER BY `id` DESC 
            LIMIT 9 
        ) T
        ORDER BY T.id ASC LIMIT 9 ";
$nitrate = mysqli_query($link, $sql_nitrate);
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Nitrate (mg/l)</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <div id="graph_nitrate" style="width:100%; height:300px;"></div>
        </div>
    </div>
</div>

