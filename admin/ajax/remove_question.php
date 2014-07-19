<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php

$ADMIN->ajax_validate();

$result         = false;
$module_id      = post_value('module_id');
$question_id    = post_value('question_id');

if (is_numeric($module_id) && is_numeric($question_id)) {

    $module     = new HuskyHuntModule(intval($module_id));
    $question   = new HuskyHuntQuestion(intval($question_id));
    $result     = $module->remove_question($question);
}

echo json_encode($result);
