<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM ".TABLE_DATA_TEMP_RPI." 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$temperature_RPI = mysqli_query($link, $sql);
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Température raspberry (°C)</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <div id="graph_temperature_rpi" style="width:100%; height:300px;"></div>
        </div>
    </div>
</div>

