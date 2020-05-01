<?php
//liste des status
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `status`
        ORDER BY `groupe` ASC";
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
            <form id="form-status" method="post" action="save" class="form-horizontal form-label-left switch-state">
                <input type="hidden" name="submit" value="1"/>
                <?php foreach ($listes_status as $status): ?>
                    <?php if ($last != $status["groupe"]): ?>
                        <div class="ln_solid"></div>
                    <?php endif; ?>
                    <?php $last = $status["groupe"] ?>

                    <div class="form-group">
                        <label class="control-label col-md-6 col-sm-6 col-xs-6"><?= $status['label'] ?></label>
                        <div class="col-md-6 col-sm-6 col-xs-6">
                            <div class="">
                                <input name="<?= $status['name'] ?>" type="checkbox"
                                           class="js-switch" <?php if ($status['value'] == '1') echo 'checked'; ?> />
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
</div>
