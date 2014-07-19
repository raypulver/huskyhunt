<?php require_once './hh-config.php'; ?>
<?php 

    $module_id = request_value('MODULE_ID');
    if (is_null($module_id))
        $module_id = session_value('MODULE_ID');

    if (is_null($module_id) || !$USER->quiz_eligible($module_id)) {
        header('Location: index.php');
        die();
    }

    $module = new HuskyHuntModule($module_id);

    $_SESSION['MODULE_ID'] = $module_id;

?>
<html>

    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div class="container" style="background: white; min-height: 200px;">
            <div class="row">
            
                <?php if (!is_null($module)) { ?> 
                
                <div class="col-md-8 col-md-offset-2">
                    <div class="well">
                        <h1> <?=$module->title?> </h1>
                        <hr /> 
                        <div><?=$module->body?> </div>
                    </div>
                </div>

                <div class="col-md-8 col-md-offset-2">
                    <button id="mobile_provider" type="button" class="pull-right btn-lg btn-primary" onclick="window.location='quiz.php';">Take The Quiz!</button>
                </div>

                <?php } ?>

            </div>
        </div>
    </body>
</html>

