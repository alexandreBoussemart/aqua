<!DOCTYPE html>
<html lang="en">
    <head>
        <?php  require 'blocs/head.php'; ?>
        <title>Aquarium - Schéma de branchement</title>
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
                    require 'blocs/messages.php';
                    require 'blocs/schema/schema.php';
                    ?>
                </div>
                <!-- /page content -->
            </div>
        </div>
        <?php  require 'blocs/js.php'; ?>
    </body>
</html>
