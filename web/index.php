<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="production/images/favicon.ico" type="./production/image/ico"/>
        <title>Aquarium</title>
        <link href="./vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="./vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link href="./build/css/custom.min.css" rel="stylesheet">
        <link href="./vendors/switchery/dist/switchery.min.css" rel="stylesheet">
    </head>

    <?php
    require 'functions.php';

    $last_debit = $last_temp = 0;
    $date = new DateTime();
    $date_debit = $date_temp = $date->format('Y-m-d H:i:s');

    $period = 1;
    if (isset($_GET['period'])) {
        $period = $_GET['period'];
    }

    $day_name = date('D');

    if ($period == 1) {
        $periode = '-1 day';
    } elseif ($period == 2) {
        $periode = '-2 days';
    } elseif ($period == 7) {
        $periode = '-7 days';
    } else {
        $periode = '-1 day';
    }

    $date = new DateTime();
    $today = $date->format('Y-m-d H:i:s');
    $date->modify($periode);
    $yesterday = $date->format('Y-m-d H:i:s');

    $sql = "SELECT * FROM `temperature` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
    $temperature = mysqli_query($link, $sql);

    $sql = "SELECT * FROM `reacteur` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
    $reacteur = mysqli_query($link, $sql);

    $sql = "SELECT * FROM `state` WHERE `path` = 'ecumeur'";
    $ecumeur = mysqli_query($link, $sql);

    while ($obj = $ecumeur->fetch_object()) {
        $state_ecumeur = $obj->value;
        $date_ecumeur = $obj->created_at;
        $date_ecumeur = new DateTime($date_ecumeur);
        $date_ecumeur = $date_ecumeur->format('d/m/Y à H:i:s');
    }

    $sql = "SELECT * FROM `state` WHERE `path` = 'bailling'";
    $bailling = mysqli_query($link, $sql);

    while ($obj = $bailling->fetch_object()) {
        $state_bailling = str_split($obj->value);
        $date_bailling = $obj->created_at;
        $date_bailling = new DateTime($date_bailling);
        $date_bailling = $date_bailling->format('d/m/Y à H:i:s');
    }

    $osmolateur_c = getStatus($link, 'osmolateur');
    $bailling_c = getStatus($link, 'bailling');
    $temperature_c = getStatus($link, 'temperature');
    $reacteur_c = getStatus($link, 'reacteur');
    $ventilateur_reacteur = getStatus($link, 'reacteur');
    $cron = getStatus($link, 'cron');
    $ecumeur_c = getStatus($link, 'ecumeur');

    if (isset($_POST['submit'])) {
        if (isset($_POST['bailling'])) {
            $value_bailling = 1;
        } else {
            $value_bailling = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value_bailling . "' WHERE `name` = 'bailling'";
        $link->query($sql);

        if (isset($_POST['osmolateur'])) {
            $value_osmolateur = 1;
        } else {
            $value_osmolateur = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value_osmolateur . "' WHERE `name` = 'osmolateur'";
        $link->query($sql);

        if (isset($_POST['ecumeur'])) {
            $value_ecumeur = 1;
        } else {
            $value_ecumeur = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value_ecumeur . "' WHERE `name` = 'ecumeur'";
        $link->query($sql);

        if (isset($_POST['temperature'])) {
            $value_temperature = 1;
        } else {
            $value_temperature = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value_temperature . "' WHERE `name` = 'temperature'";
        $link->query($sql);

        if (isset($_POST['reacteur'])) {
            $value_reacteur = 1;
        } else {
            $value_reacteur = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value_reacteur . "' WHERE `name` = 'reacteur'";
        $link->query($sql);

        if (isset($_POST['ventilateur_reacteur'])) {
            $value_reacteur_ventilateur = 1;
        } else {
            $value_reacteur_ventilateur = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value_reacteur_ventilateur . "' WHERE `name` = 'reacteur'";
        $link->query($sql);

        if (isset($_POST['cron'])) {
            $value_cron = 1;
        } else {
            $value_cron = 0;
        }
        $sql = "UPDATE `status` SET `value`='" . $value_cron . "' WHERE `name` = 'cron'";
        $link->query($sql);

        header('Location: '.$data['database'][0]['base_url']); ///aqua-web
    }

    $sql = "SELECT `created_at` FROM `controle` WHERE `value` = 'controle_osmolateur'";
    $request = mysqli_query($link, $sql);
    while ($obj = $request->fetch_object()) {
        $log = $obj->created_at;
        $log = new DateTime($log);
        $log_osmolateur = $log->format('d/m/Y à H:i:s');
    }

    $sql = "SELECT `created_at` FROM `controle` WHERE `value` = 'controle_temperature'";
    $request = mysqli_query($link, $sql);
    while ($obj = $request->fetch_object()) {
        $log = $obj->created_at;
        $log = new DateTime($log);
        $log_temperature = $log->format('d/m/Y à H:i:s');
    }

    $sql = "SELECT `created_at` FROM `controle` WHERE `value` = 'controle_bailling'";
    $request = mysqli_query($link, $sql);
    while ($obj = $request->fetch_object()) {
        $log = $obj->created_at;
        $log = new DateTime($log);
        $log_bailling = $log->format('d/m/Y à H:i:s');
    }

    $sql = "SELECT `created_at` FROM `controle` WHERE `value` = 'controle_reacteur'";
    $request = mysqli_query($link, $sql);
    while ($obj = $request->fetch_object()) {
        $log = $obj->created_at;
        $log = new DateTime($log);
        $log_reacteur = $log->format('d/m/Y à H:i:s');
    }

    $sql = "SELECT `created_at` FROM `controle` WHERE `value` = 'controle_ecumeur'";
    $request = mysqli_query($link, $sql);
    while ($obj = $request->fetch_object()) {
        $log = $obj->created_at;
        $log = new DateTime($log);
        $log_ecumeur = $log->format('d/m/Y à H:i:s');
    }

    $sql = "SELECT * FROM `osmolateur` where `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "' order by created_at DESC";
    $osmo = mysqli_query($link, $sql);

    $sql = "SELECT count(*) as somme FROM `osmolateur` WHERE `state` = 'pump_on' and `created_at` >= '" . $yesterday . "' and `created_at` <= '" . $today . "'";
    $count = mysqli_query($link, $sql);
    while ($obj = $count->fetch_object()) {
        $somme = $obj->somme;
    }

    $sql = "SELECT `value`,`created_at` FROM `reacteur` ORDER BY `reacteur`.`id`  DESC LIMIT 1";
    $request = mysqli_query($link, $sql);
    while ($obj = $request->fetch_object()) {
        $last_debit = $obj->value;
        $log = $obj->created_at;
        $log = new DateTime($log);
        $date_debit = $log->format('d/m/Y à H:i:s');
    }

    $sql = "SELECT `value`,`created_at` FROM `temperature` ORDER BY `temperature`.`id` DESC LIMIT 1";
    $request = mysqli_query($link, $sql);
    while ($obj = $request->fetch_object()) {
        $last_temp = round($obj->value, 2);
        $log = $obj->created_at;
        $log = new DateTime($log);
        $date_temp = $log->format('d/m/Y à H:i:s');
    }



    ?>

    <body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <div class="col-md-3 left_col">
                <div class="left_col scroll-view">
                    <div class="navbar nav_title" style="border: 0;">
                        <a href="#" class="site_title"> </a>
                    </div>
                    <div class="clearfix"></div>
                    <div class="profile clearfix">
                        <div class="profile_info">
                            <span>Bonjour,</span>
                            <h2>Alexandre</h2>
                        </div>
                    </div>
                    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                        <div class="menu_section">
                            <h3>Général</h3>
                            <ul class="nav side-menu">
                                <li><a><i class="fa fa-home"></i> Accueil <span class="fa fa-chevron-down"></span></a>
                                    <ul class="nav child_menu">
                                        <li><a href="index.php">Dashboard</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- top navigation -->
            <div class="top_nav">
                <div class="nav_menu">
                    <nav>
                        <div class="nav toggle">
                            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                        </div>
                        <ul class="nav navbar-nav navbar-right">
                            <li role="presentation" class="dropdown">
                                <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown"
                                   aria-expanded="false">
                                    <i class="fa fa-envelope-o"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <!-- /top navigation -->

            <!-- page content -->
            <div class="right_col" role="main">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Période</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <a href="index.php?period=1"
                                   class="btn <?php if ($period == '1') echo 'btn-danger'; else echo 'btn-default'; ?>">1
                                    jour</a>
                                <a href="index.php?period=2"
                                   class="btn <?php if ($period == '2') echo 'btn-danger'; else echo 'btn-default'; ?>">2
                                    jours</a>
                                <a href="index.php?period=7"
                                   class="btn <?php if ($period == '7') echo 'btn-danger'; else echo 'btn-default'; ?>">7
                                    jours</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 content-calendrier">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Calendrier</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <div class="calendrier">
                                    <div class="days <?php if($day_name == "Mon"): ?>today<?php endif; ?>">
                                        <div class="title">Lundi</div>
                                        <div class="contenu">
                                            <div class="event surgele">
                                                Nourriture congelée
                                            </div>
                                            <div class="event coraux">
                                                Nourriture coraux
                                            </div>
                                            <div class="event bacterie">
                                                Bactérie
                                            </div>
                                            <div class="event algue">
                                                Algue
                                            </div>
                                        </div>
                                    </div>
                                    <div class="days <?php if($day_name == "Tue"): ?>today<?php endif; ?>"">
                                        <div class="title">Mardi</div>
                                        <div class="contenu">
                                            <div class="event surgele">
                                                Nourriture congelée
                                            </div>
                                        </div>
                                    </div>
                                    <div class="days <?php if($day_name == "Wed"): ?>today<?php endif; ?>"">
                                        <div class="title">Mercredi</div>
                                        <div class="contenu">
                                            <div class="event surgele">
                                                Nourriture congelée
                                            </div>
                                            <div class="event coraux">
                                                Nourriture coraux
                                            </div>
                                            <div class="event algue">
                                                Algue
                                            </div>
                                        </div>
                                    </div>
                                    <div class="days <?php if($day_name == "Thu"): ?>today<?php endif; ?>"">
                                        <div class="title">Jeudi</div>
                                        <div class="contenu">
                                            <div class="event surgele">
                                                Nourriture congelée
                                            </div>
                                        </div>
                                    </div>
                                    <div class="days <?php if($day_name == "Fri"): ?>today<?php endif; ?>"">
                                        <div class="title">Vendredi</div>
                                        <div class="contenu">
                                            <div class="event surgele">
                                                Nourriture congelée
                                            </div>
                                            <div class="event coraux">
                                                Nourriture coraux
                                            </div>
                                            <div class="event algue">
                                                Algue
                                            </div>
                                        </div>
                                    </div>
                                    <div class="days <?php if($day_name == "Sat"): ?>today<?php endif; ?>"">
                                        <div class="title">Samedi</div>
                                        <div class="contenu">
                                            <div class="event surgele">
                                                Nourriture congelée
                                            </div>
                                        </div>
                                    </div>
                                    <div class="days <?php if($day_name == "Sun"): ?>today<?php endif; ?>"">
                                        <div class="title">Dimanche</div>
                                        <div class="contenu">
                                            <div class="event surgele">
                                                Nourriture congelée
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- top tiles -->
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
                    <div class="col-md-3 col-sm-3 col-xs-12 tile_stats_count <?php if ($state_ecumeur == '0') echo 'error'; ?>">
                        <span class="count_top"><i class="fa fa-power-off"></i> Écumeur</span>
                        <div class="count"><?php if ($state_ecumeur == '0') echo 'ERREUR'; else echo 'OK'; ?></div>
                        <span class="count_bottom">Dernière mise à jour le <?= $date_ecumeur ?></span>
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
                </div>
                <!-- /top tiles -->

                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Température</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content2">
                                <div id="graph_temperature" style="width:100%; height:300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Débit</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content2">
                                <div id="graph_debit" style="width:100%; height:300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title osmo">
                                <h2>Osmolateur<small><?php echo $somme; ?> remplissage(s)</small></h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content">
                                <ul class="list-unstyled timeline">
                                    <?php while ($obj = $osmo->fetch_object()) {
                                        $event_date = $obj->created_at;
                                        $event_date = new DateTime($event_date);
                                        $event_date = $event_date->format('d/m/Y à H:i:s'); ?>

                                        <li>
                                            <div class="block <?php echo $obj->state ?>">
                                                <div class="tags">
                                                    <a href="" class="tag">
                                                        <span></span>
                                                    </a>
                                                </div>
                                                <div class="block_content">
                                                    <h2 class="title">
                                                        <a><?php echo getLabel($obj->state); ?></a>
                                                    </h2>
                                                    <div class="byline">
                                                        <span><?php echo $event_date; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Configuration
                                    <small>On/Off</small>
                                </h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content2">
                                <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                                    <input type="hidden" name="submit" value="1"/>
                                    <div class="form-group">
                                        <label class="control-label col-md-6 col-sm-6 col-xs-6">Osmolateur</label>
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <div class="">
                                                <label>
                                                    <input name="osmolateur" type="checkbox"
                                                           class="js-switch" <?php if ($osmolateur_c == '1') echo 'checked'; ?> />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label  col-md-6 col-sm-6 col-xs-6">Écumeur</label>
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <div class="">
                                                <label>
                                                    <input name="ecumeur" type="checkbox"
                                                           class="js-switch" <?php if ($ecumeur_c == '1') echo 'checked'; ?>/>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-6 col-sm-6 col-xs-6">Température</label>
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <div class="">
                                                <label>
                                                    <input name="temperature" type="checkbox"
                                                           class="js-switch" <?php if ($temperature_c == '1') echo 'checked'; ?>/>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-6 col-sm-6 col-xs-6">Bailling</label>
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <div class="">
                                                <label>
                                                    <input name="bailling" type="checkbox"
                                                           class="js-switch" <?php if ($bailling_c == '1') echo 'checked'; ?> />
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-6 col-sm-6 col-xs-6">Réacteur</label>
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <div class="">
                                                <label>
                                                    <input name="reacteur" type="checkbox"
                                                           class="js-switch" <?php if ($reacteur_c == '1') echo 'checked'; ?>/>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-6 col-sm-6 col-xs-6">Ventilateur réacteur</label>
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <div class="">
                                                <label>
                                                    <input name="ventilateur_reacteur" type="checkbox"
                                                           class="js-switch" <?php if ($ventilateur_reacteur == '1') echo 'checked'; ?>/>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-6 col-sm-6 col-xs-6">Cron</label>
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <div class="">
                                                <label>
                                                    <input name="cron" type="checkbox"
                                                           class="js-switch" <?php if ($cron == '1') echo 'checked'; ?>/>
                                                </label>
                                            </div>
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
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <div class="x_panel">
                            <div class="x_title">
                                <h2>Log</h2>
                                <div class="clearfix"></div>
                            </div>
                            <div class="x_content2 log">
                                <ul class="quick-list">
                                    <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase">Osmolateur </strong>Dernière
                                            mise à jour le <strong><?php echo $log_osmolateur ?></strong></a></li>
                                    <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase">Température </strong>Dernière
                                            mise à jour le <strong><?php echo $log_temperature ?></strong></a></li>
                                    <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase">Bailling </strong>Dernière
                                            mise à jour le <strong><?php echo $log_bailling ?></strong></a></li>
                                    <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase">Écumeur </strong>Dernière
                                            mise à jour le <strong><?php echo $log_ecumeur ?></strong></a></li>
                                    <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase">Réacteur </strong>Dernière
                                            mise à jour le <strong><?php echo $log_reacteur ?></strong></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /page content -->
        </div>
    </div>

    <script src="./vendors/jquery/dist/jquery.min.js"></script>
    <script src="./vendors/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="./build/js/custom.min.js"></script>
    <script src="./vendors/raphael/raphael.min.js"></script>
    <script src="./vendors/morris.js/morris.min.js"></script>
    <script src="./vendors/switchery/dist/switchery.min.js"></script>

    <script>
        if ($('#graph_temperature').length) {
            Morris.Line({
                element: 'graph_temperature',
                xkey: 'datetime',
                ykeys: ['value'],
                labels: ['Value'],
                goals: [23, 25, 28],
                goalLineColors: ['#2B46F0', '#7FFF00', '#d43f3a'],
                goalStrokeWidth: '2',
                pointStrokeColors: ['#FF4500'],
                hideHover: 'auto',
                ymin: 10,
                pointSize: 1,
                lineColors: ['#FF4500'],
                data: [
                    <?php while($obj = $temperature->fetch_object()){ ?>
                    {datetime: '<?= $obj->created_at; ?>', value: <?= $obj->value; ?>},
                    <?php } ?>
                ],
                resize: true
            });

            $MENU_TOGGLE.on('click', function () {
                $(window).resize();
            });
        }

        if ($('#graph_debit').length) {
            Morris.Line({
                element: 'graph_debit',
                xkey: 'datetime',
                ykeys: ['value'],
                labels: ['Value'],
                goals: [1200],
                goalLineColors: ['#2B46F0'],
                goalStrokeWidth: '2',
                pointStrokeColors: ['#FF4500'],
                hideHover: 'auto',
                ymin: 1100,
                ymax: 1500,
                pointSize: 1,
                lineColors: ['#FF4500'],
                data: [
                    <?php while($obj = $reacteur->fetch_object()){ ?>
                    {datetime: '<?= $obj->created_at; ?>', value: <?= $obj->value; ?>},
                    <?php } ?>
                ],
                resize: true
            });

            $MENU_TOGGLE.on('click', function () {
                $(window).resize();
            });
        }
    </script>

    </body>
</html>
