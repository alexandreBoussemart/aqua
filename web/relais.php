<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require '../app/code/templates/head.php'; ?>
        <title>Reef Pi - Test relais</title>
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
                    <?php
                    require '../app/code/templates/relais/form.php';
                    ?>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require '../app/code/templates/footer.php'; ?>
    </body>
</html>

<?php session_write_close(); ?>