<?php
// logs
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT * 
        FROM `log` 
        ORDER BY `id` DESC 
        LIMIT 30;";
$request = mysqli_query($link, $sql);
$logs = mysqli_query($link, $sql);

// changement eau
$sql = "# noinspection SqlNoDataSourceInspectionForFile  
        SELECT * 
        FROM `data_changement_eau` 
        ORDER BY `id` DESC 
        LIMIT 30;";
$request = mysqli_query($link, $sql);
$changements = mysqli_query($link, $sql);
?>

<div class="row first-bloc">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Schéma de branchement</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content2">
                <table class="schema">
                    <tr>
                        <td>Capteur<br> osmolateur<br> réserve</td>
                        <td>Bouton<br> poussoir<br> Osmolateur</td>
                        <td>Interrupteur<br> pompe<br> Osmolateur</td>
                    </tr>
                    <tr>
                        <td>Pompe<br> Osmolateur</td>
                        <td>Capteur<br> Osmolateur</td>
                        <td>Ventilateur<br> Aquarium</td>
                    </tr>
                    <tr>
                        <td>Capteur<br> Bailling</td>
                        <td>Capteur<br> Température</td>
                        <td>Capteur<br> Écumeur</td>
                    </tr>
                    <tr>
                        <td>Réacteur<br> Éclairage<br> Ventilateur</td>
                        <td>Capteur<br> Réacteur</td>
                        <td></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
