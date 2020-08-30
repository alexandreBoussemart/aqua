<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title title-relais <?php if(getStateRelais($link, "refroidissement")): ?>on<?php else: ?>off<?php endif; ?>">
                <h2>Relais ventilateur boitier</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_ventilateur_boitier_on" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-success">On</button>
                    </div>
                </div>
            </form>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_ventilateur_boitier_off" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-danger">Off</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title title-relais <?php if(getStateRelais($link, "aquarium_ventilateur")): ?>on<?php else: ?>off<?php endif; ?>">
                <h2>Relais ventilateur aquarium</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_ventilateur_aquarium_on" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-success">On</button>
                    </div>
                </div>
            </form>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_ventilateur_aquarium_off" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-danger">Off</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title title-relais <?php if(getStateRelais($link, "ecumeur")): ?>on<?php else: ?>off<?php endif; ?>">
                <h2>Relais écumeur</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_ecumeur_on" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-success">On</button>
                    </div>
                </div>
            </form>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_ecumeur_off" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-danger">Off</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title title-relais <?php if(getStateRelais($link, "osmolateur")): ?>on<?php else: ?>off<?php endif; ?>">
                <h2>Relais osmolateur</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_osmolateur_on" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-success">On</button>
                    </div>
                </div>
            </form>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_osmolateur_off" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-danger">Off</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title title-relais <?php if(getStateRelais($link, "pompe_osmolateur")): ?>on<?php else: ?>off<?php endif; ?>">
                <h2>Relais pompe osmolateur</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_pompe_osmolateur_on" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-success">On</button>
                    </div>
                </div>
            </form>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_pompe_osmolateur_off" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-danger">Off</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title title-relais <?php if(getStateRelais($link, "reacteur_eclairage")): ?>on<?php else: ?>off<?php endif; ?>">
                <h2>Relais réacteur éclairage</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_reacteur_eclairage_on" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-success">On</button>
                    </div>
                </div>
            </form>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_reacteur_eclairage_off" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-danger">Off</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title title-relais <?php if(getStateRelais($link, "reacteur_ventilateur")): ?>on<?php else: ?>off<?php endif; ?>">
                <h2>Relais réacteur ventilateur</h2>
                <div class="clearfix"></div>
            </div>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_reacteur_ventilateur_on" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-success">On</button>
                    </div>
                </div>
            </form>
            <form method="post" action="controller/execRelais"
                  class="form-relais form-horizontal form-label-left switch-state">
                <input type="hidden" name="relais_reacteur_ventilateur_off" value="1"/>
                <div class="form-group">
                    <div class="">
                        <button type="submit" class="btn btn-danger">Off</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>