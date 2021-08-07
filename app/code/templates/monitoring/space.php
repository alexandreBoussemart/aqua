<?php
exec("df -Bh /tmp | tail -1 | awk '{print $4}'", $output);
var_dump($output);

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

