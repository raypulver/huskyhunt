<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php 

$ADMIN->ajax_validate();

$mid    = post_value('mid');

$module = (is_numeric($mid)) ? new HuskyHuntModule(intval($mid)) ? NULL

if (!is_null($module)) {
    $module->delete();
}

echo json_encode($result);

