<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require 'blocs/head.php'; ?>
        <title>Aquarium - Dashboard</title>
    </head>

    <?php
    require 'blocs/header.php';
    ?>

    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                <?php  require 'blocs/menu.php'; ?>
                <?php  require 'blocs/top_nav.php'; ?>

                <!-- page content -->
                <div class="right_col" role="main">
                    <?php
                    require 'blocs/calendrier.php';
                    require 'blocs/state.php';
                    require 'blocs/graph.php';
                    require 'blocs/graph_param.php';
                    ?>
                    <br>
                    <div class="row">
                        <?php
                        require 'blocs/analyse_eau.php';
                        require 'blocs/controle.php';
                        ?>
                    </div>
                    <div class="row">
                        <?php
                        require 'blocs/changement_eau.php';
                        ?>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'blocs/js.php'; ?>
    </body>
</html>
