<?php
$sql_phosphate = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT T.*
        FROM (
            SELECT * 
            FROM `data_parametres_eau` 
            WHERE `type` LIKE 'phosphate' 
            ORDER BY `id` DESC 
            LIMIT 9 
        ) T
        ORDER BY T.id ASC LIMIT 9 ";
$phosphate = mysqli_query($link, $sql_phosphate);
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Phosphate (mg/l)</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <div id="graph_phosphate" style="width:100%; height:300px;"></div>
        </div>
    </div>
</div>

