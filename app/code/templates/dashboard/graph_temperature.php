<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `data_reacteur` 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$reacteur = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `data_temperature_air` 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$temperature_air = mysqli_query($link, $sql);
?>

<div class="row first-bloc">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Température eau (°C)</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <div id="graph_temperature_eau" style="width:100%; height:300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Température air (°C)</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <div id="graph_temperature_air" style="width:100%; height:300px;"></div>
            </div>
        </div>
    </div>
</div>
