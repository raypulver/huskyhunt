<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php

$ADMIN->ajax_validate();

$module_id  = post_value('module_id');
$start      = post_value('start');
$stop       = post_value('stop');
$result     = false;

$module = new HuskyHuntModule($module_id);

$result = $module->add_timeline($start, $stop);

echo json_encode($result);
