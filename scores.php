<?php require_once './hh-config.php'; ?>
<?php $huskyhunt = new HuskyHunt(); ?>
<html>

    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <?php $huskyhunt->render_scores_table(); ?> 
                </div>
            </div>
        </div>
    </body>
</html>

