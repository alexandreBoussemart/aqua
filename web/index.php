<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require '../app/code/templates/head.php'; ?>
        <title>Aquarium - Dashboard</title>
    </head>

    <?php require '../app/code/templates/header.php'; ?>

    <body class="nav-md">
        <?php  require '../app/code/templates/start_body.php'; ?>
        <div class="container body">
            <div class="main_container">
                <?php  require '../app/code/templates/menu.php'; ?>
                <?php  require '../app/code/templates/top_nav.php'; ?>

                <!-- page content -->
                <div class="right_col" role="main">
                    <?php
                    require '../app/code/templates/messages.php';
                    require '../app/code/templates/dashboard/fast_actions.php';
                    require '../app/code/templates/dashboard/calendrier.php';
                    require '../app/code/templates/dashboard/state.php';
                    require '../app/code/templates/dashboard/graph.php';
                    require '../app/code/templates/dashboard/graph_param.php';
                    ?>
                    <div class="row">
                        <?php
                        require '../app/code/templates/dashboard/analyse_eau.php';
                        require '../app/code/templates/dashboard/controle.php';
                        ?>
                    </div>
                    <div class="row">
                        <?php
                        require '../app/code/templates/dashboard/changement_eau.php';
                        ?>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require '../app/code/templates/footer.php'; ?>
        <?php  require '../app/code/templates/js.php'; ?>
    </body>
</html>
