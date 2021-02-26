<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require '../app/code/templates/head.php'; ?>
        <title>Reef Pi - Dashboard</title>
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
                    <?php require '../app/code/templates/messages.php'; ?>
                    <div class="row">
                        <?php require '../app/code/templates/dashboard/fast_actions.php'; ?>
                        <?php require '../app/code/templates/dashboard/todo.php'; ?>
                    </div>
                    <?php
                    require '../app/code/templates/dashboard/calendrier.php';
                    require '../app/code/templates/dashboard/state.php';
                    require '../app/code/templates/dashboard/graph_temperature.php';
                    ?>
                    <div class="row">
                        <?php
                        require '../app/code/templates/dashboard/graph_debit.php';
                        require '../app/code/templates/dashboard/changement_eau.php';
                        ?>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require '../app/code/templates/footer.php'; ?>
        <?php  require '../app/code/templates/dashboard/js.php'; ?>
    </body>
</html>

<?php session_write_close(); ?>