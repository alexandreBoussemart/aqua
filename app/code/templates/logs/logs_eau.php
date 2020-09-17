<?php
// changement eau
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT * 
        FROM `data_changement_eau` 
        ORDER BY `id` DESC 
        LIMIT 50;";
$changements = mysqli_query($link, $sql);

// logs
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT SUM(`value`) as sum
        FROM `data_changement_eau`";
$total_litre = mysqli_query($link, $sql);
$total_litre = mysqli_fetch_assoc($total_litre);
$total_litre = $total_litre['sum'];
?>

<div class="row first-bloc">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Changement d'eau
                    <small><?php echo $total_litre; ?> litre<?php if($total_litre > 1):?>s<?php endif;?></small>
                </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <table id="datatable-eau" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th style="width: 25px;">Id</th>
                        <th style="width: 25%;">Date</th>
                        <th>Volume</th>
                        <th style="width: 25px;">Supprimer</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($changements as $changement): ?>
                        <tr>
                            <td><?= $changement["id"] ?></td>
                            <td><?= getFormattedDate($changement["created_at"]) ?></td>
                            <td><?= $changement["value"] ?> Litres</td>
                            <td class="action_grid">
                                <form method="post" action="controller/save"
                                      class="form-horizontal form-label-left switch-state">
                                    <input type="hidden" name="submit_delete_eau" value="1"/>
                                    <input type="hidden" name="id" value="<?= $changement["id"] ?>"/>
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
