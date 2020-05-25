<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Analyse d'eau</h2>
            <div class="clearfix"></div>
        </div>
        <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
            <input type="hidden" name="submit_params" value="1"/>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-3">Ca<span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <input name="value_ca" class="date-picker form-control col-md-7 col-xs-12"
                           type="text"/>
                    <p class="info">
                        <?= getLastParam($link, 'ca'); ?><?= getLastDiffParam($link, 'ca'); ?>
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-3">Kh<span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <input name="value_kh" class="date-picker form-control col-md-7 col-xs-12"
                           type="text"/>
                    <p class="info">
                        <?= getLastParam($link, 'kh'); ?><?= getLastDiffParam($link, 'kh'); ?>
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-3">Mg<span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <input name="value_mg" class="date-picker form-control col-md-7 col-xs-12"
                           type="text"/>
                    <p class="info">
                        <?= getLastParam($link, 'mg'); ?><?= getLastDiffParam($link, 'mg'); ?>
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-3">Densité<span
                            class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <input name="value_densite" class="date-picker form-control col-md-7 col-xs-12"
                           type="text"/>
                    <p class="info">
                        <?= getLastParam($link, 'densite'); ?><?= getLastDiffParam($link, 'densite'); ?>
                    </p>
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
