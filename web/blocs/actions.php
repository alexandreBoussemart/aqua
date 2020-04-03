<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
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
</div>
