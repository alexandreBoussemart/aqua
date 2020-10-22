<?php
// crons
$crons = getCrons();
?>

<div class="row first-bloc">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Liste cron</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <table id="datatable" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 25%;">Type</th>
                        <th>Run</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($crons as $cron): ?>
                        <tr>
                            <td><?= $cron["type"] ?></td>
                            <td>
                                <button
                                        data-file="<?= $cron["run"] ?>"
                                        class="btn btn-default btn-run"
                                        type="submit">
                                    Run
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
