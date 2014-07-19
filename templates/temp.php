<html>
    <head>
        <?php include BASE_PATH . '/include.php'; ?>
    </head>
    <body>

        <div class="navbar">
            <div class="container">
            <img class="pull-left uconn-wordmark" src="<?=BASE_URL?>/images/uconn-wordmark.png" />
            <img class="pull-left huskyhunt-paw" src="<?=BASE_URL?>/images/light-paw.png" />
            
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">HuskyHunt!</a></li>
            </ul>

            <ul class="pull-right nav navbar-nav">
                <?php if (1) { ?> <li><a href="<?=BASE_URL?>/admin">Admin</a></li> <?php } ?>
                <li><a href="#">Scores</a></li>
                <li><a href="#">Help</a></li>
            </ul>



            </div>
        </div>

        <div class="container" style="background: white; min-height: 200px;">
            HuskyHunt
        </div>


    </body>
</html>
