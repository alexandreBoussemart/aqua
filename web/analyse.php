<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require '../app/code/templates/head.php'; ?>
        <title>Reef Pi - Monitoring système</title>
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
                    ?>
                    <div class="row">
                        <?php
                        require '../app/code/templates/analyse/analyse_eau.php';
                        ?>
                    </div>
                    <div class="row first-bloc">
                        <?php
                        require '../app/code/templates/analyse/ca.php';
                        require '../app/code/templates/analyse/kh.php';
                        ?>
                    </div>
                    <div class="row first-bloc">
                        <?php
                        require '../app/code/templates/analyse/mg.php';
                        require '../app/code/templates/analyse/densite.php';
                        ?>
                    </div>
                    <div class="row first-bloc">
                        <?php
                        require '../app/code/templates/analyse/nitrate.php';
                        require '../app/code/templates/analyse/phosphate.php';
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

<?php session_write_close(); ?>