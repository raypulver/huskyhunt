<?php require_once './hh-config.php'; ?>
<?php 

    $huskyhunt = new HuskyHunt();
    $module_id = $huskyhunt->daily_module();

    if (!is_null($module_id) && $USER->quiz_eligible($module_id)) {
        $_SESSION['MODULE_ID'] = $module_id;
        redirect('module.php');
    }

    redirect('index.php');
    
