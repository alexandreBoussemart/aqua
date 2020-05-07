<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require 'templates/head.php'; ?>
        <title>Aquarium - Configuration</title>
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
                    require 'templates/configuration/actions.php';
                    ?>
                    <div class="row">
                        <?php

                        require 'templates/configuration/configuration.php';
                        require 'templates/configuration/status.php';
                        ?>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'templates/js.php'; ?>
    </body>
</html>
