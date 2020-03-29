<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="src/images/favicon.ico" type="./src/image/ico"/>
        <title>Aquarium</title>
        <link href="./vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="./vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link href="./build/css/custom.min.css" rel="stylesheet">
        <link href="./vendors/switchery/dist/switchery.min.css" rel="stylesheet">
        <!-- Datatables -->
        <link href="./vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
        <link href="./vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
        <link href="./vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
        <link href="./vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
        <link href="./vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">
    </head>

    <?php
    require 'blocs/header.php';
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

                <?php  require 'blocs/top_nav.php'; ?>

                <!-- page content -->
                <div class="right_col" role="main">
                    <?php
                    require 'blocs/days.php';
                    require 'blocs/calendrier.php';
                    require 'blocs/state.php';
                    require 'blocs/graph.php';
                    require 'blocs/graph_param.php';
                    require 'blocs/logs.php';
                    ?>
                    <br>
                    <div class="row">
                        <?php
                        require 'blocs/osmolateur.php';
                        require 'blocs/status.php';
                        require 'blocs/controle.php';
                        ?>
                    </div>
                    <?php
                    require 'blocs/actions.php';
                    require 'blocs/logs2.php';
                    ?>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'blocs/js.php'; ?>
    </body>
</html>
