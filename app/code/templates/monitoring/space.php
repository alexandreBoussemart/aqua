<?php
exec("df -BM /tmp | tail -1 | awk '{print $4}'", $output);
$used = (int)str_replace('G', '', $output[0]);
$used = $used / 1000;

echo round($used, 2)
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Espace disque</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2 log">
            aze
        </div>
    </div>
</div>

