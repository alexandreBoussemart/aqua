<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require 'templates/head.php'; ?>
        <title>Aquarium - Dashboard</title>
    </head>

    <?php require 'templates/header.php'; ?>

    <body class="nav-md">
        <?php  require 'templates/start_body.php'; ?>
        <div class="container body">
            <div class="main_container">
                <?php  require 'templates/menu.php'; ?>
                <?php  require 'templates/top_nav.php'; ?>

                <!-- page content -->
                <div class="right_col" role="main">
                    <?php
                    require 'templates/messages.php';
                    require 'templates/dashboard/fast_actions.php';
                    require 'templates/dashboard/calendrier.php';
                    require 'templates/dashboard/state.php';
                    require 'templates/dashboard/graph.php';
                    require 'templates/dashboard/graph_param.php';
                    ?>
                    <div class="row">
                        <?php
                        require 'templates/dashboard/analyse_eau.php';
                        require 'templates/dashboard/controle.php';
                        ?>
                    </div>
                    <div class="row">
                        <?php
                        require 'templates/dashboard/changement_eau.php';
                        ?>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'templates/js.php'; ?>
    </body>
</html>
