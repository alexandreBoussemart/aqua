<?php
// logs
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT SUM(`value`) as sum
        FROM `data_depense`";
$total = mysqli_query($link, $sql);
$total = mysqli_fetch_assoc($total);
?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Total</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content total-budget">
                <?php
                $number = $total['sum'];
                echo number_format($number, 2, ',', ' ')."€";
                ?>
            </div>
        </div>
    </div>
</div>