<div class="col-md-3 col-sm-3 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Contrôle</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content2 log">
            <ul class="quick-list">
                <?php foreach ($listes_controles as $controle): ?>
                    <li><i class="fa fa-check-square-o"></i><a href="#"><strong style="text-transform: uppercase"><?= utf8_encode($controle['label']) ?> </strong>Dernière
                            mise à jour le <strong><?= getFormattedDate($controle['created_at']) ?></strong></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
