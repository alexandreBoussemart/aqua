<?php
// logs
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT * 
        FROM `data_depense` 
        ORDER BY `data_depense`.`created_at` DESC";
$budget = mysqli_query($link, $sql);
?>

<div class="row first-bloc">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Suivi budget</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <table id="datatable_budget" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 25px;">Id</th>
                        <th style="display: none;">Date tri</th>
                        <th style="width: 25%;">Date</th>
                        <th>Commentaire</th>
                        <th>Prix</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($budget as $log): ?>
                        <tr>
                            <td><?= $log["id"] ?></td>
                            <td class="hidden"><?= $log["created_at"] ?></td>
                            <td data-sort="<?= $log["created_at"] ?>"><?= getFormattedDateWithouH($log["created_at"]) ?></td>
                            <td><?= $log["comment"] ?></td>
                            <td><?= number_format($log["value"], 2, ',', ' ') ?>€</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
