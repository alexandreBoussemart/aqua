<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile
      SELECT s.`error` as error, s.`label` as label, la.`created_at` as date
      FROM ".TABLE_STATE." s
      INNER JOIN ".TABLE_LAST_ACTIVITY." as la ON la.`value` = s.`path` 
      WHERE `path` LIKE 'controle_%'";
$states_cron = mysqli_query($link, $sql);
?>

<div class="row tile_count">
    <?php while ($obj = $states_cron->fetch_object()): ?>
        <div class="col-md-1-5 col-sm-1-5 col-xs-12 tile_stats_count <?php if ($obj->error == '1') echo 'error'; ?>">
            <span class="count_top"><i class="fa fa-power-off"></i> <?= $obj->label ?></span>
            <div class="count"><?php if ($obj->error == '1') echo 'ERREUR'; else echo 'OK'; ?></div>
            <span class="count_bottom">Dernière mise à jour le <?= getFormattedDate($obj->date); ?></span>
        </div>
    <?php endwhile; ?>
</div>

<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile
        SELECT * 
        FROM ".TABLE_STATE." 
        WHERE `path` IN ('".OSMOLATEUR."','".ECUMEUR."','".REACTEUR."','".TEMPERATURE."');";
$states = mysqli_query($link, $sql);
?>

<div class="row tile_count">
    <?php while ($obj = $states->fetch_object()): ?>
        <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($obj->error == '1') echo 'error'; ?>">
            <span class="count_top"><i class="fa fa-power-off"></i> <?= $obj->label ?></span>
            <div class="count"><?php if ($obj->error == '1') echo 'ERREUR'; else echo 'OK'; ?>
                <?php
                $message = '';
                if ($obj->path == TEMPERATURE && $obj->error == '0') {
                    $message = getLastData($link, "data_temperature_eau", " °C");
                } elseif ($obj->path == REACTEUR && $obj->error == '0' && $obj->value != 'state_97') {
                    $message = getLastData($link, "data_reacteur", " l/min");
                } else {
                    $message = explode('-', $obj->message);
                    $message = end($message);
                }
                ?>
                <small><?= trim($message) ?></small>
            </div>
            <span class="count_bottom">Dernière mise à jour le <?= getFormattedDate($obj->created_at); ?></span>
        </div>
    <?php endwhile; ?>
</div>

<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile
        SELECT * 
        FROM ".TABLE_STATE." 
        WHERE `path` = 'bailling'";
$bailling = mysqli_query($link, $sql);
$row = mysqli_fetch_assoc($bailling);
$state_bailling = $row['value'];
$date_bailling = getFormattedDate($row['created_at']);

$errorBailling1 = ['state_1', 'state_2', 'state_3', 'state_5', 'state_9', 'state_10'];
$errorBailling2 = ['state_2', 'state_4', 'state_5', 'state_6', 'state_9', 'state_10'];
$errorBailling3 = ['state_3', 'state_4', 'state_5', 'state_7', 'state_9', 'state_10'];
?>

<div class="row tile_count">
    <div class="col-md-4 col-sm-4 col-xs-12 tile_stats_count <?php if (in_array($state_bailling, $errorBailling1)) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 1</span>
        <div class="count"><?php if (in_array($state_bailling, $errorBailling1)) echo 'ERREUR'; else echo 'OK'; ?>
            <?php
            $message = '';
            if ($state_bailling == 'state_99'):
                $message = explode('-', $row['message']);
                $message = end($message);
            ?>
            <small><?= trim($message) ?></small>
            <?php endif; ?>
        </div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12 tile_stats_count <?php if (in_array($state_bailling, $errorBailling2)) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 2</span>
        <div class="count"><?php if (in_array($state_bailling, $errorBailling2)) echo 'ERREUR'; else echo 'OK'; ?>
            <?php
            $message = '';
            if ($state_bailling == 'state_99'):
                $message = explode('-', $row['message']);
                $message = end($message);
                ?>
                <small><?= trim($message) ?></small>
            <?php endif; ?>
        </div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12 tile_stats_count <?php if (in_array($state_bailling, $errorBailling3)) echo 'error'; ?>">
        <span class="count_top"><i class="fa fa-power-off"></i> Bailling 3</span>
        <div class="count"><?php if (in_array($state_bailling, $errorBailling3)) echo 'ERREUR'; else echo 'OK'; ?>
            <?php
            $message = '';
            if ($state_bailling == 'state_99'):
                $message = explode('-', $row['message']);
                $message = end($message);
                ?>
                <small><?= trim($message) ?></small>
            <?php endif; ?>
        </div>
        <span class="count_bottom">Dernière mise à jour le <?= $date_bailling ?></span>
    </div>
</div>

