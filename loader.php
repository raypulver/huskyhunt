<?php require_once './hh-config.php'; ?>
<?php 

    $module_id  = request_value('module_id');
    $target     = request_value('target');


    if (is_numeric($module_id))
        $_SESSION['MODULE_ID'] = $module_id;

    if (!is_null($target)) {

        if (strtoupper($target) == 'SHARE')
            header('Location: share.php');
        elseif (strtoupper($target) == 'QUIZ')
            header('Location: quiz.php');
        elseif (strtoupper($target) == 'MODULE')
            header('Location: module.php');
        else 
            header('Location: index.php');
        die();
    }

    header('Location: index.php');
    die();
