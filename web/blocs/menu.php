<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="#" class="site_title"> </a>
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
                    <li><a><i class="fa fa-home"></i> Accueil <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="<?= $data['database'][0]['base_url']; ?>">Dashboard</a></li>
                            <li><a href="<?= $data['database'][0]['base_url']; ?>configuration.php">Configuration</a></li>
                            <li><a href="<?= $data['database'][0]['base_url']; ?>schema.php">Schéma de branchement</a></li>
                            <li><a href="<?= $data['database'][0]['base_url']; ?>logs.php">Log</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
