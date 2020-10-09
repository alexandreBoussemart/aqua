<?php if (isset($_SESSION['success']) && count($_SESSION['success']) > 0): ?>
    <?php foreach ($_SESSION['success'] as $data): ?>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="alert alert-success" role="alert">
                    <?= $data ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['success']) ?>
<?php endif; ?>

<?php if (isset($_SESSION['error']) && count($_SESSION['error']) > 0): ?>
    <?php foreach ($_SESSION['error'] as $data): ?>
        <br>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="alert alert-error" role="alert">
                    <?= $data ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['error']) ?>
<?php endif; ?>






