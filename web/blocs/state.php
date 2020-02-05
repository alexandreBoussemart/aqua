<?php
$sql = "SELECT * FROM `state` WHERE `path` = 'ecumeur' LIMIT 1";
$ecumeur = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($ecumeur);
$state_ecumeur = $row['value'];
$date_ecumeur = getFormattedDate($row['created_at']);

$sql = "SELECT * FROM `state` WHERE `path` = 'bailling'";
$bailling = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($bailling);
$state_bailling = $row['value'];
$date_bailling = getFormattedDate($row['created_at']);

$errorBailling1 = ['state_1', 'state_2', 'state_3', 'state_5', 'state_9', 'state_10'];
$errorBailling2 = ['state_2', 'state_4', 'state_5', 'state_6', 'state_9', 'state_10'];
$errorBailling3 = ['state_3', 'state_4', 'state_5', 'state_7', 'state_9', 'state_10'];

$sql = "SELECT `error` FROM `state` WHERE `path` = 'controle_reacteur' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_reacteur = $row['error'];
$sql = "SELECT `created_at` FROM `last_activity` WHERE `value` = 'controle_reacteur' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_reacteur_date = getFormattedDate($row['created_at']);

$sql = "SELECT `error` FROM `state` WHERE `path` = 'controle_ecumeur' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_ecumeur = $row['error'];
$sql = "SELECT `created_at` FROM `last_activity` WHERE `value` = 'controle_ecumeur' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_ecumeur_date = getFormattedDate($row['created_at']);

$sql = "SELECT `error` FROM `state` WHERE `path` = 'controle_bailling' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_bailling = $row['error'];
$sql = "SELECT `created_at` FROM `last_activity` WHERE `value` = 'controle_bailling' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_bailling_date = getFormattedDate($row['created_at']);

$sql = "SELECT `error` FROM `state` WHERE `path` = 'controle_osmolateur' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_osmolateur = $row['error'];
$sql = "SELECT `created_at` FROM `last_activity` WHERE `value` = 'controle_osmolateur' LIMIT 1";
$result = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($result);
$state_controle_osmolateur_date = getFormattedDate($row['created_at']);

//dernier osmo
$sql = "SELECT * FROM `data_osmolateur` ORDER BY `data_osmolateur`.`id` DESC LIMIT 1";
$request = mysqli_query($link, $sql);
$last_omo = mysqli_fetch_assoc($request);

if (!isset($last_omo)) {
    $last_omo['state'] = 'error';
    $last_omo['created_at'] = Date('Y-m-d H:i:s');
}

// dernier débit
$sql = "SELECT `value`,`created_at` FROM `data_reacteur` ORDER BY `data_reacteur`.`id`  DESC LIMIT 1";
$request = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($request);
$last_debit = $row['value'];
$date_debit = getFormattedDate($row['created_at']);

if (!isset($last_debit)) {
    $last_debit = 0;
}

// dernière temperature
$sql = "SELECT `value`,`created_at` FROM `data_temperature` ORDER BY `data_temperature`.`id` DESC LIMIT 1";
$request = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($request);
$last_temp = round($row['value'], 2);
$date_temp = getFormattedDate($row['created_at']);

?>


<div class="row tile_count">
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if (in_array($state_bailling, $errorBailling1)) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 1</span>
        <div class="count"><?php if (in_array($state_bailling, $errorBailling1)) echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if (in_array($state_bailling, $errorBailling2)) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 2</span>
        <div class="count"><?php if (in_array($state_bailling, $errorBailling2)) echo 'ERREUR'; else echo 'OK'; ?></div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if (in_array($state_bailling, $errorBailling3)) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 3</span>
        <div class="count"><?php if (in_array($state_bailling, $errorBailling3)) echo 'ERREUR'; else echo 'OK'; ?></div>
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
