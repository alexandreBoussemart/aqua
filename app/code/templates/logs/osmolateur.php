<?php
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM `data_osmolateur` 
        WHERE `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "' 
        ORDER BY created_at DESC";
$osmo = mysqli_query($link, $sql);

$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT count(*) as somme 
        FROM `data_osmolateur` 
        WHERE `state` = 'pump_on' 
        AND `created_at` >= '" . $yesterday . "' 
        AND `created_at` <= '" . $today . "'";
$count = mysqli_query($link, $sql);
while ($obj = $count->fetch_object()) {
    $count_osmolateur = $obj->somme;
}
?>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title osmo">
                <h2>Osmolateur
                    <small><?php echo $count_osmolateur; ?> remplissage<?php if($count_osmolateur > 1):?>s<?php endif;?></small>
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <ul class="list-unstyled timeline">
                    <?php while ($obj = $osmo->fetch_object()) {
                        $event_date = $obj->created_at;
                        $event_date = new DateTime($event_date);
                        $event_date = $event_date->format('d/m/Y à H:i:s'); ?>

                        <li>
                            <div class="block <?php echo $obj->state ?>">
                                <div class="tags">
                                    <a href="" class="tag">
                                        <span></span>
                                    </a>
                                </div>
                                <div class="block_content">
                                    <h2 class="title">
                                        <a><?php echo getLabel($obj->state); ?></a>
                                    </h2>
                                    <div class="byline">
                                        <span><?php echo $event_date; ?></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>