<div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Changement d'eau</h2>
            <div class="clearfix"></div>
        </div>
        <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
            <input type="hidden" name="submit_eau" value="1"/>
            <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-3">Volume<span class="required">*</span>
                </label>
                <div class="col-md-9 col-sm-9 col-xs-9">
                    <input name="value" class="date-picker form-control col-md-7 col-xs-12" required="required"
                           type="number" pattern="[0-9]*" inputmode="numeric"/>
                    <p class="info">
                        <?= getLastChangementEau($link); ?>
                    </p>
                </div>
            </div>
            <div class="ln_solid"></div>
            <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
