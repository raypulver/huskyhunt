<?php require_once './hh-config.php'; ?>
<?php $huskyhunt = new HuskyHunt(); ?>
<html>

    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div class="container">
            <?php $huskyhunt->render_knowledge_base(); ?> 
        </div>
    </body>
</html>

