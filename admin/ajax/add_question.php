<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php

$ADMIN->ajax_validate();

$result         = false;
$module_id      = post_value('module_id');
$question_id    = post_value('question_id');

if (is_numeric($module_id)) {

    $module = new HuskyHuntModule(intval($module_id));
    $question = (is_numeric($question_id)) ? new HuskyHuntQuestion(intval($question_id)) : new HuskyHuntQuestion();
    $result = $module->add_question($question);
}

if ($result) 
    echo $question->json_encode();
else
    echo json_encode($result);
