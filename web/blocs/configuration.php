<?php
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

<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Configuration</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2">
            <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                <input type="hidden" name="submit_configuration" value="1"/>
                <br>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Température déclenchement ventilateur<span
                            class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['config_temperature_declenchement']; ?>"
                               name="config_temperature_declenchement" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">On/Off osmolateur</label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <div class="">
                            <label>
                                <input name="config_on_off_osmolateur" type="checkbox"
                                       class="js-switch" <?php if ($config['config_on_off_osmolateur'] == '1') {
                                    echo 'checked';
                                } ?> />
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">On/Off écumeur</label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <div class="">
                            <label>
                                <input name="config_on_off_ecumeur" type="checkbox"
                                       class="js-switch" <?php if ($config['config_on_off_ecumeur'] == '1') {
                                    echo 'checked';
                                } ?> />
                            </label>
                        </div>
                    </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Activer log in files</label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <div class="">
                            <label>
                                <input name="config_log_in_files" type="checkbox"
                                       class="js-switch" <?php if ($config['config_log_in_files'] == '1') {
                                    echo 'checked';
                                } ?> />
                            </label>
                        </div>
                    </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Nombre de jours avant alert changement d'eau<span
                                class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['check_changement_eau']; ?>"
                               name="check_changement_eau" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Nombre de jours avant alert nettoyage réacteur<span
                                class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['check_clean_reacteur']; ?>"
                               name="check_clean_reacteur" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Nombre de jours avant alert nettoyage écumeur<span
                                class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['check_clean_ecumeur']; ?>"
                               name="check_clean_ecumeur" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Nombre de jours avant alert nettoyage pompes<span
                                class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['check_clean_pompes']; ?>"
                               name="check_clean_pompes" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Nombre de jours avant alert analyse d'eau<span
                                class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['check_analyse_eau']; ?>"
                               name="check_analyse_eau" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Température autorisée minimum<span
                                class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['temperature_min']; ?>"
                               name="temperature_min" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-6 col-sm-6 col-xs-6">Température autorisée maximum<span
                                class="required">*</span>
                    </label>
                    <div class="col-md-6 col-sm-6 col-xs-6">
                        <input value="<?= $config['temperature_max']; ?>"
                               name="temperature_max" class="form-control col-md-7 col-xs-12"
                               required="required" type="text">
                    </div>
                </div>

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
