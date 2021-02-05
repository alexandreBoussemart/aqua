<?php
//liste des status
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM ".TABLE_STATUS."
        WHERE `name` = 'on_off_osmolateur'
        OR `name` = 'on_off_ecumeur'
        ORDER BY `groupe` ASC";
$listes_status_rapides = mysqli_query($link, $sql);
$last = '1';
?>

<?php
$checks = allCheckLastTimeCheck("", "", $link, false);
$checks = array_filter($checks);
?>

<?php if (count($checks) > 0): ?>
    <div class="col-md-6 col-xs-12 col-sm-12">
<?php else: ?>
    <div class="col-md-12 col-xs-12 col-sm-12">
<?php endif; ?>
    <div class="x_panel">
        <div class="x_title">
            <h2>Actions rapides</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form id="actions-rapides" method="post" action="controller/saveActionsRapide"
                  class="form-horizontal form-label-left switch-state">
                <?php foreach ($listes_status_rapides as $status): ?>
                    <div class="form-group form-actions-rapides">
                        <label class="control-label"><?= $status['label'] ?></label>
                        <div class="">
                            <div class="js-switch-label">
                                <label for="<?= $status['name'] ?>"></label>
                                <input name="<?= $status['name'] ?>" type="checkbox"
                                       class="js-switch <?= $status['name'] ?>" <?php if ($status['value'] == '1') echo 'checked'; ?> />
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</div>
