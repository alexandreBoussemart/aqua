<?php
// logs
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT * 
        FROM ".TABLE_LOG_MAIL." 
        ORDER BY `id` DESC 
        LIMIT 50;";
$logs_mails = mysqli_query($link, $sql);

?>

<div class="row first-bloc">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Log mail</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <table id="datatable-mail" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 25px;">Id</th>
                        <th style="width: 25%;">Date</th>
                        <th>Sujet</th>
                        <th>Message</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs_mails as $log): ?>
                        <tr>
                            <td><?= $log["id"] ?></td>
                            <td><?= getFormattedDate($log["created_at"]) ?></td>
                            <td><?= $log["sujet"] ?></td>
                            <td><?= $log["message"] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
