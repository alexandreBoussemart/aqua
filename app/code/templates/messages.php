<?php if (isset($_SESSION['success']) && count($_SESSION['success']) > 0): ?>
    <?php foreach ($_SESSION['success'] as $message): ?>
        <div class="messages" class="row">
            <div class="alert alert-success" role="alert">
                <?= $message ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['success']) ?>
<?php endif; ?>

<?php if (isset($_SESSION['error']) && count($_SESSION['error']) > 0): ?>
    <?php foreach ($_SESSION['error'] as $message): ?>
        <div class="messages" class="row">
            <div class="alert alert-error" role="alert">
                <?= $message ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['error']) ?>
<?php endif; ?>






