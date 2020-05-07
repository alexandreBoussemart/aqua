<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require 'templates/head.php'; ?>
        <title>Aquarium - Schéma de branchement</title>
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
                    require 'templates/schema/schema.php';
                    ?>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'templates/js.php'; ?>
    </body>
</html>
