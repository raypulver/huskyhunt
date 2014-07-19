<?php require_once '../hh-config.php'; ?>
<?php 
    if (!is_null($USER)) {
        $module = $USER->next_module();
    }
?>
<html>

    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div class="container" style="background: white; min-height: 200px;">
            <div class="row">
                <div class="well" style="background: red;">
                <h1 style="text-align: center;"> Access Denied! </h1>
                </div>
            </div>
        </div>
    </body>
</html>

