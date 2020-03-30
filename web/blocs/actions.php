<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Actions</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_actions_clear" value="1"/>
                    <button type="submit" class="btn btn-default">Vider BDD > 30 jours</button>
                </form>
            </div>
            <div class="x_content">
                <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_actions_clear_reacteur" value="1"/>
                    <button type="submit" class="btn btn-default">Nettoyage réacteur</button>
                    <small><?= getDateLastCleanReacteur($link) ?></small>
                </form>
            </div>
            <div class="x_content">
                <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_actions_clear_ecumeur" value="1"/>
                    <button type="submit" class="btn btn-default">Nettoyage écumeur</button>
                    <small><?= getDateLastCleanEcumeur($link) ?></small>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Analyse d'eau</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                <input type="hidden" name="submit_params" value="1"/>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Ca<span class="required">*</span></label>
                    <div class="col-md-9 col-sm-9 col-xs-9">
                        <input name="value_ca" class="date-picker form-control col-md-7 col-xs-12"
                               type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Kh<span class="required">*</span></label>
                    <div class="col-md-9 col-sm-9 col-xs-9">
                        <input name="value_kh" class="date-picker form-control col-md-7 col-xs-12"
                               type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Mg<span class="required">*</span></label>
                    <div class="col-md-9 col-sm-9 col-xs-9">
                        <input name="value_mg" class="date-picker form-control col-md-7 col-xs-12"
                               type="text">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3 col-xs-3">Densité<span
                                class="required">*</span></label>
                    <div class="col-md-9 col-sm-9 col-xs-9">
                        <input name="value_densite" class="date-picker form-control col-md-7 col-xs-12"
                               type="text">
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
