<div class="col-md-6 col-xs-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Avancée</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content progress-content">
            <?php $numberNow = getDaysProgess($link, 'data_parametres_eau', $type = 'ca') ?>
            <?php $numberMax = getConfig($link, 'check_analyse_eau'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Test Ca <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-warning"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_parametres_eau', $type = 'mg') ?>
            <?php $numberMax = getConfig($link, 'check_analyse_eau'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Test Mg <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-warning"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_parametres_eau', $type = 'kh') ?>
            <?php $numberMax = getConfig($link, 'check_analyse_eau'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Test Kh <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-warning"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_parametres_eau', $type = 'densite') ?>
            <?php $numberMax = getConfig($link, 'check_analyse_eau'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Test densité <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-warning"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_parametres_eau', $type = 'nitrate') ?>
            <?php $numberMax = getConfig($link, 'check_analyse_eau'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Test nitrate <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-warning"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_parametres_eau', $type = 'phosphate') ?>
            <?php $numberMax = getConfig($link, 'check_analyse_eau'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Test phosphate <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-warning"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_changement_eau') ?>
            <?php $numberMax = getConfig($link, 'check_changement_eau'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Changement d'eau <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_clean_reacteur') ?>
            <?php $numberMax = getConfig($link, 'check_clean_reacteur'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Nettoyage réacteur <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-success"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_clean_ecumeur') ?>
            <?php $numberMax = getConfig($link, 'check_clean_ecumeur'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Nettoyage écumeur <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-success"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
            <?php $numberNow = getDaysProgess($link, 'data_clean_pompes') ?>
            <?php $numberMax = getConfig($link, 'check_clean_pompes'); ?>
            <?php $tempsRestant = max(0, $numberMax - $numberNow); ?>
            <?php $transitiongoal = getTransitiongoal($numberNow, $numberMax) ?>
            <p>
                Nettoyages pompes <small>(<?= $numberNow; ?>/<?= $numberMax; ?>J)</small>
                <span><small>Temps restant : <?= $tempsRestant ?>J</small></span>
            </p>
            <div class="progress active">
                <div class="progress-bar progress-bar-striped progress-bar-success"
                     role="progressbar"
                     data-transitiongoal="<?= $transitiongoal; ?>">
                </div>
            </div>
        </div>
    </div>
</div>
