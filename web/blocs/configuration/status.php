<?php
//liste des status
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `status`";
$listes_status = mysqli_query($link, $sql);
$last = '1';
?>
<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Statut
                <small>On/Off</small>
            </h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <form method="post" action="save.php" class="form-horizontal form-label-left switch-state">
                <input type="hidden" name="submit" value="1"/>
                <?php foreach ($listes_status as $status): ?>
                    <?php if ($last != $status[4]): ?>
                        <div class="ln_solid"></div>
                    <?php endif; ?>
                    <?php $last = $status[4] ?>

                    <div class="form-group">
                        <label class="control-label col-md-6 col-sm-6 col-xs-6"><?= $status['label'] ?></label>
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="">
                                <label>
                                    <input name="<?= $status['name'] ?>" type="checkbox"
                                           class="js-switch" <?php if ($status['value'] == '1') echo 'checked'; ?> />
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
