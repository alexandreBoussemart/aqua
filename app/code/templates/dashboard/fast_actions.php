<?php
//liste des status
$sql = "# noinspection SqlNoDataSourceInspectionForFile 
        SELECT * 
        FROM " . TABLE_STATUS . "
        WHERE `name` = 'on_off_osmolateur'
        OR `name` = 'on_off_ecumeur'
        ORDER BY `groupe` ASC";
$listes_status_rapides = mysqli_query($link, $sql);
$last = '1';

$ecumeurHaveTimer = haveTimer($link, ECUMEUR);
$reacteurHaveTimer = haveTimer($link, REACTEUR);
$osmolateurHaveTimer = haveTimer($link, OSMOLATEUR);

?>

<div class="col-md-6 col-xs-12 col-sm-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Actions rapides</h2>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <form id="actions-rapides" method="post" action="controller/saveActionsRapide"
                  class="form-horizontal form-label-left switch-state">
                <?php foreach ($listes_status_rapides as $status): ?>
                    <div class="form-group form-actions-rapides">
                        <label class="control-label"><?= $status['label'] ?></label>
                        <div class="">
                            <div class="js-switch-label">
                                <label for="<?= $status['name'] ?>"></label>
                                <input name="<?= $status['name'] ?>" type="checkbox"
                                       class="js-switch <?= $status['name'] ?>" <?php if ($status['value'] == '1') echo 'checked'; ?> />
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
            <div class="form-group form-timer">
                <div class="timer-forms">
                    <form method="post" action="controller/saveTimer"
                          class="form-horizontal form-label-left switch-timer">
                        <input type="hidden" name="timer_ecumeur" value="1"/>
                        <button type="submit"
                                class="btn btn-default <?php if ($ecumeurHaveTimer): echo 'haveTimer'; else: echo 'noTimer'; endif; ?>">
                            <i class="fa fa-clock-o"></i> Pause écumeur
                        </button>
                    </form>
                    <?php if ($ecumeurHaveTimer): ?>
                        <form method="post" action="controller/saveTimer"
                              class="form-horizontal form-label-left switch-timer">
                            <input type="hidden" name="remove_timer_ecumeur" value="1"/>
                            <button type="submit" class="btn">
                                <i class="fa fa-close"></i>
                            </button>
                        </form>
                        <p>jusqu'à <?= getFormattedHours(getTimer($link, ECUMEUR), $link) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group form-timer">
                <div class="timer-forms">
                    <form method="post" action="controller/saveTimer"
                          class="form-horizontal form-label-left switch-timer">
                        <input type="hidden" name="timer_reacteur" value="1"/>
                        <button type="submit"
                                class="btn btn-default <?php if ($reacteurHaveTimer): echo 'haveTimer'; else: echo 'noTimer'; endif; ?>">
                            <i class="fa fa-clock-o"></i> Pause réacteur
                        </button>
                    </form>
                    <?php if ($reacteurHaveTimer): ?>
                        <form method="post" action="controller/saveTimer"
                              class="form-horizontal form-label-left switch-timer">
                            <input type="hidden" name="remove_timer_reacteur" value="1"/>
                            <button type="submit" class="btn">
                                <i class="fa fa-close"></i>
                            </button>
                        </form>
                        <p>jusqu'à <?= getFormattedHours(getTimer($link, REACTEUR), $link) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-group form-timer">
                <div class="timer-forms">
                    <form method="post" action="controller/saveTimer"
                          class="form-horizontal form-label-left switch-timer">
                        <input type="hidden" name="timer_osmolateur" value="1"/>
                        <button type="submit"
                                class="btn btn-default <?php if ($osmolateurHaveTimer): echo 'haveTimer'; else: echo 'noTimer'; endif; ?>">
                            <i class="fa fa-clock-o"></i> Pause osmolateur
                        </button>
                    </form>
                    <?php if ($osmolateurHaveTimer): ?>
                        <form method="post" action="controller/saveTimer"
                              class="form-horizontal form-label-left switch-timer">
                            <input type="hidden" name="remove_timer_osmolateur" value="1"/>
                            <button type="submit" class="btn">
                                <i class="fa fa-close"></i>
                            </button>
                        </form>
                        <p>jusqu'à <?= getFormattedHours(getTimer($link, OSMOLATEUR), $link) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
