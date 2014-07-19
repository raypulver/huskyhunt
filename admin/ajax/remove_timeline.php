<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php

$ADMIN->ajax_validate();

$result         = false;
$timeline_id    = post_value('timeline_id');
$module_id      = post_value('module_id');

$module   = new HuskyHuntModule($module_id);

$result = $module->remove_timeline($timeline_id);


echo json_encode($result);
