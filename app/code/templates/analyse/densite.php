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
