<div class="row first-bloc">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Log</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <table id="datatable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 25px;">Id</th>
                            <th style="width: 25%;">Date</th>
                            <th>Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log["id"] ?></td>
                                <td><?= getFormattedDate($log["created_at"]) ?></td>
                                <td><?= $log["message"] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Changement d'eau</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                    <input type="hidden" name="submit_eau" value="1"/>

                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Volume <span class="required">*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input name="value" class="date-picker form-control col-md-7 col-xs-12" required="required" type="text">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success">Enregistrer</button>
                        </div>
                    </div>
                    <div class="ln_solid"></div>

                </form>

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
                                <form method="post" action="index.php" class="form-horizontal form-label-left switch-state">
                                    <input type="hidden" name="submit_delete_eau" value="1"/>
                                    <input type="hidden" name="id" value="<?= $changement["id"] ?>"/>
                                    <button class="btn btn-default" type="submit"><i class="fa fa-close"></i>Supprimer</button>
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
