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
?>

<div class="row">
    <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Kh</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <div id="graph_kh" style="width:100%; height:300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Ca</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <div id="graph_ca" style="width:100%; height:300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Mg</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <div id="graph_mg" style="width:100%; height:300px;"></div>
            </div>
        </div>
    </div>
</div>
