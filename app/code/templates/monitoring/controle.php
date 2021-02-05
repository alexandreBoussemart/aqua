<?php
//liste des controles
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM ".TABLE_LAST_ACTIVITY."";
$listes_controles = mysqli_query($link, $sql);
?>

<div class="col-md-6 col-sm-6 col-xs-12">
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

