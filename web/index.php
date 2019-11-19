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
    require 'header.php';
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
                                    <h2>Osmolateur<small><?php echo $count_osmolateur; ?> remplissage(s)</small></h2>
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
                                    <h2>Statut
                                        <small>On/Off</small>
                                    </h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content2">
                                    <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                                        <input type="hidden" name="submit" value="1"/>
                                        <?php foreach ($listes_status as $status): ?>
                                        <div class="form-group">
                                            <label class="control-label col-md-6 col-sm-6 col-xs-6"><?= utf8_encode($status['label']) ?></label>
                                            <div class="col-md-6 col-sm-6 col-xs-6">
                                                <div class="">
                                                    <label>
                                                        <input name="<?= utf8_encode($status['name']) ?>" type="checkbox"
                                                               class="js-switch" <?php if ($status['value'] == '1') echo 'checked'; ?> />
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
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
                                    <h2>Contrôle</h2>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content2 log">
                                    <ul class="quick-list">
                                        <?php foreach ($listes_controles as $controle): ?>
                                        <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase"><?= utf8_encode($controle['label']) ?> </strong>Dernière
                                                mise à jour le <strong><?= getFormattedDate($controle['created_at']) ?></strong></a></li>
                                        <?php endforeach; ?>
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
