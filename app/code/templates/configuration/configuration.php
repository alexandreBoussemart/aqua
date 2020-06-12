<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `core_config`
        ORDER BY `groupe` ASC";
$core_config = mysqli_query($link, $sql);
$core_config = mysqli_fetch_all($core_config);
$last = '1';
?>

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Configuration</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
                <input type="hidden" name="submit_configuration" value="1"/>
                <br>
                <?php foreach ($core_config as $c): ?>
                    <?php if ($last != $c[4]): ?>
                        <div class="ln_solid"></div>
                    <?php endif; ?>
                    <?php $last = $c[4] ?>

                    <?php if ($c[3] == "string"): ?>
                        <div class="form-group">
                            <label class="control-label col-md-6 col-sm-6 col-xs-6"><?= $c[5] ?><span
                                        class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <input value="<?= $c[2] ?>"
                                       name="<?= $c[1] ?>" class="form-control col-md-7 col-xs-12"
                                       required="required" type="text">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($c[3] == "numeric"): ?>
                        <div class="form-group">
                            <label class="control-label col-md-6 col-sm-6 col-xs-6"><?= $c[5] ?><span
                                        class="required">*</span>
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <input value="<?= $c[2] ?>"
                                       name="<?= $c[1] ?>" class="form-control col-md-7 col-xs-12"
                                       required="required" type="number" pattern="[0-9]*" inputmode="numeric">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($c[3] == "bool"): ?>
                        <div class="form-group">
                            <label class="control-label col-md-6 col-sm-6 col-xs-6"><?= $c[5] ?></label>
                            <div class="col-md-6 col-sm-6 col-xs-6">
                                <div class="">
                                    <label>
                                        <input name="<?= $c[1] ?>" type="checkbox"
                                               class="js-switch" <?php if ($c[2] == '1') {
                                            echo 'checked';
                                        } ?> />
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
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
