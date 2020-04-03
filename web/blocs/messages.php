<?php
session_start();
?>

<?php if (isset($_SESSION['message']) && $_SESSION['message'] != ""): ?>
    <br>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <?php if ($_SESSION['result'] == "success"): ?>
                <div class="alert alert-success" role="alert">
                    <?= $_SESSION['message'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $data['result'] = "";
    $data['message'] = "";
    $_SESSION = $data;
    session_write_close();
    ?>

<?php endif; ?>




