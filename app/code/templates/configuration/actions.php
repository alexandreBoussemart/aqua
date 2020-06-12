<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12 actions">
        <div class="x_panel">
            <div class="x_title">
                <h2>Actions</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_actions_clear" value="1"/>
                    <button onclick="return confirm('Êtes-vous sûr de vouloir vider les données de BDD > 30 jours ?')"
                            type="submit" class="btn btn-default">Vider BDD > 30 jours
                    </button>
                    <p class="info"><?= getOlderData($link) ?></p>
                </form>
            </div>
            <div class="x_content">
                <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_actions_clear_reacteur" value="1"/>
                    <button onclick="return confirm('Êtes-vous sûr de vouloir marquer le réacteur comme nettoyé ?')"
                            type="submit" class="btn btn-default">Nettoyage réacteur
                    </button>
                    <p class="info"><?= getDateLastClean($link, "reacteur") ?></p>
                </form>
            </div>
            <div class="x_content">
                <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_actions_clear_ecumeur" value="1"/>
                    <button onclick="return confirm('Êtes-vous sûr de vouloir marquer l\'écumeur comme nettoyé ?')"
                            type="submit" class="btn btn-default">Nettoyage écumeur
                    </button>
                    <p class="info"><?= getDateLastClean($link, "ecumeur") ?></p>
                </form>
            </div>
            <div class="x_content">
                <form method="post" action="controller/save" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_actions_clear_pompes" value="1"/>
                    <button onclick="return confirm('Êtes-vous sûr de vouloir marquer les pompes comme nettoyé ?')"
                            type="submit" class="btn btn-default">Nettoyage pompes
                    </button>
                    <p class="info"><?= getDateLastClean($link, "pompes") ?></p>
                </form>
            </div>
        </div>
    </div>
</div>
