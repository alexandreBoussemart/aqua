<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="<?= $data['database'][0]['base_url']; ?>" class="site_title">
                <img src="src/images/favicon.ico" alt="Récifal" />
                <span>App récifal</span>
            </a>
        </div>
        <div class="clearfix"></div>
        <div class="profile clearfix">
            <div class="profile_info">
                <span>Bonjour,</span>
                <h2>Alexandre</h2>
            </div>
        </div>
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <h3>Général</h3>
                <ul class="nav side-menu">
                    <li>
                        <a href="<?= $data['database'][0]['base_url']; ?>">
                            <i class="fa fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?= $data['database'][0]['base_url']; ?>configuration">
                            <i class="fa fa-wrench"></i> Configuration
                        </a>
                    </li>
                    <li>
                        <a href="<?= $data['database'][0]['base_url']; ?>schema">
                            <i class="fa fa-map-o"></i> Schéma de branchement
                        </a>
                    </li>

                    <li>
                        <a href="<?= $data['database'][0]['base_url']; ?>logs">
                            <i class="fa fa-hdd-o"></i> Log
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>