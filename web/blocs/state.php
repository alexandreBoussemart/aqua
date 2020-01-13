<div class="row tile_count">
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_bailling[0] == '0') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 1</span>
        <div class="count"><?php if ($state_bailling[0] == '0') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_bailling[1] == '0') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 2</span>
        <div class="count"><?php if ($state_bailling[1] == '0') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_bailling[2] == '0') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 3</span>
        <div class="count"><?php if ($state_bailling[2] == '0') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_ecumeur == 'state_1') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Écumeur</span>
        <div class="count"><?php if ($state_ecumeur == 'state_1') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_ecumeur ?></span>
    </div>
</div>

<div class="row tile_count">
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_controle_ecumeur == '1') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Script écumeur</span>
        <div class="count"><?php if ($state_controle_ecumeur == '1') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $state_controle_ecumeur_date ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_controle_reacteur == '1') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Script réacteur</span>
        <div class="count"><?php if ($state_controle_reacteur == '1') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $state_controle_reacteur_date ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_controle_bailling == '1') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Script balling</span>
        <div class="count"><?php if ($state_controle_bailling == '1') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $state_controle_bailling_date ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_controle_osmolateur == '1') echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Script osmolateur</span>
        <div class="count"><?php if ($state_controle_osmolateur == '1') echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $state_controle_osmolateur_date ?></span>
    </div>
</div>

<div class="row tile_count">
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($last_debit < 1200) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Réacteur</span>
        <div class="count"><?php if ($last_debit < 1200) echo 'ERREUR <small>'.$last_debit.' l/m</small>'; else echo 'OK <small>'.$last_debit.' l/min</small>'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_debit ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($last_temp > 28 || $last_temp < 23) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Température</span>
        <div class="count"><?php if ($last_temp > 28 || $last_temp < 23) echo 'ERREUR <small>'.$last_temp.'°C</small>'; else echo 'OK <small>'.$last_temp.'°C</small>'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_temp ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count osmolateur <?= $last_omo['state']; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Osmolateur</span>
        <div class="count">
            <p><?php if ($last_omo['state'] != "ok") echo 'ERREUR'; else echo 'OK'; ?></p>
            <?= '<small>'.getLabel($last_omo['state']).'</small>'; ?>
        </div>
        <span class="count_bottom">Dernière mise à jour le <?= getFormattedDate($last_omo['created_at']) ?></span>
    </div>
</div>
