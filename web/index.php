<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require 'blocs/head.php'; ?>
        <title>Aquarium - Dashboard</title>
    </head>

    <?php require 'blocs/header.php'; ?>

    <body class="nav-md">
        <?php  require 'blocs/start_body.php'; ?>
        <div class="container body">
            <div class="main_container">
                <?php  require 'blocs/menu.php'; ?>
                <?php  require 'blocs/top_nav.php'; ?>

                <!-- page content -->
                <div class="right_col" role="main">
                    <?php
                    require 'blocs/messages.php';
                    require 'blocs/dashboard/fast_actions.php';
                    require 'blocs/dashboard/calendrier.php';
                    require 'blocs/dashboard/state.php';
                    require 'blocs/dashboard/graph.php';
                    require 'blocs/dashboard/graph_param.php';
                    ?>
                    <div class="row">
                        <?php
                        require 'blocs/dashboard/analyse_eau.php';
                        require 'blocs/dashboard/controle.php';
                        ?>
                    </div>
                    <div class="row">
                        <?php
                        require 'blocs/dashboard/changement_eau.php';
                        ?>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'blocs/js.php'; ?>
    </body>
</html>
