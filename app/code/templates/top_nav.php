<!-- top navigation -->
<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li class="logo-mobile">
                    <a href="<?= $data['database'][0]['base_url']; ?>" class="logo-m">
                        <span>Aquarium</span>
                    </a>
                </li>
                <li class="heure">
                    <span><?= date("d/m/Y");  ?></span>
                    &nbsp;<p><?= date("H:i:s");  ?></p>
                </li>
            </ul>
        </nav>
    </div>
</div>
<!-- /top navigation -->
