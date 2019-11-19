<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Période</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <a href="index.php?period=1"
                   class="btn <?php if ($period == '1') echo 'btn-danger'; else echo 'btn-default'; ?>">1
                    jour</a>
                <a href="index.php?period=2"
                   class="btn <?php if ($period == '2') echo 'btn-danger'; else echo 'btn-default'; ?>">2
                    jours</a>
                <a href="index.php?period=7"
                   class="btn <?php if ($period == '7') echo 'btn-danger'; else echo 'btn-default'; ?>">7
                    jours</a>
            </div>
        </div>
    </div>
</div>