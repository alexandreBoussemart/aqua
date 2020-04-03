<?php
//liste des controles
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `last_activity`";
$listes_controles = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `core_config`";
$core_config = mysqli_query($link, $sql);
$core_config = mysqli_fetch_all($core_config);
$config = [];
foreach ($core_config as $c) {
    $config[$c[1]] = $c[2];
}
?>

<div class="col-md-3 col-sm-3 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Dernière activité</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2 log">
            <ul class="quick-list">
                <?php foreach ($listes_controles as $controle): ?>
                    <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase">
                                <?= $controle['label'] ?> </strong>Dernière mise à jour le
                            <strong><?= getFormattedDate($controle['created_at']) ?></strong></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

