        <div class="navbar" ng-controller="navCtrl">
            <div class="container">
            <img class="pull-left uconn-wordmark" src="<?=BASE_URL?>/images/uconn-wordmark.png" />
            <img class="pull-left huskyhunt-paw" src="<?=BASE_URL?>/images/light-paw.png" />
            
            <ul class="nav navbar-nav">
                <li class="brand"><a href="<?=BASE_URL?>/admin">HuskyHunt Admin</a></li>
            </ul>

            <ul class="pull-right nav navbar-nav">
                <?php if (1) { /* ?> <li><a href="<?=BASE_URL?>/admin">Admin</a></li> <?php */ } ?>
                <li class="link <?=$_SERVER['REQUEST_URI'] == '/admin/scores.php' ? 'active' : ''?>"><a href="scores.php">Scores</a></li>
                <li class="link <?=$_SERVER['REQUEST_URI'] == '/admin/players.php' ? 'active' : ''?>"><a href="players.php">Player Admin</a></li>
                <li class="link <?=$_SERVER['REQUEST_URI'] == '/admin/list_modules.php' ? 'active' : ''?>"><a href="list_modules.php">Module Admin</a></li>
                <li class="link" ng-if="loggedIn"><a href="logout.php">Logout</a></li>

            </ul>

            </div>
        </div>
