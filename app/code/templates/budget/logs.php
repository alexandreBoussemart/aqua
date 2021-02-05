<?php
// logs
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT * 
        FROM ".TABLE_DATA_DEPENSE." 
        ORDER BY ".TABLE_DATA_DEPENSE.".`created_at` DESC";
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
                        <th style="width: 25px;">Supprimer</th>
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
                            <td class="action_grid">
                                <form method="post" action="controller/save"
                                      class="form-horizontal form-label-left switch-state">
                                    <input type="hidden" name="submit_delete_budget" value="1"/>
                                    <input type="hidden" name="id" value="<?= $log["id"] ?>"/>
                                    <button onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette donnée ?')"
                                            class="btn btn-default" type="submit"><i class="fa fa-close"></i>Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
