<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM ".TABLE_DATA_TEMP_EAU." 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$temperature = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM ".TABLE_DATA_REACTEUR." 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$reacteur = mysqli_query($link, $sql);
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Débit (l/h)</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <div id="graph_debit" style="width:100%; height:300px;"></div>
        </div>
    </div>
</div>
