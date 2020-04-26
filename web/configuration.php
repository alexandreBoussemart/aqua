<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require 'blocs/head.php'; ?>
        <title>Aquarium - Configuration</title>
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
                    require 'blocs/configuration/actions.php';
                    ?>
                    <div class="row">
                        <?php

                        require 'blocs/configuration/configuration.php';
                        require 'blocs/configuration/status.php';
                        ?>
                    </div>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'blocs/js.php'; ?>
    </body>
</html>
