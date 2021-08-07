<?php
exec("df -BM /tmp | tail -1 | awk '{print $4}'", $output);
$used = (int)str_replace('G', '', $output[0]);
$used = $used / 1000;
$used = round($used, 2);
$transitiongoal = getTransitiongoal($used, 32)
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Espace disque</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2 log">
            <p>
                Espace utilisé <small>(<?= $used; ?>G/<?= 32; ?>G)</small>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-warning"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
        </div>
    </div>
</div>


