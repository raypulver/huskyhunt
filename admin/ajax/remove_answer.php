<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php

$ADMIN->ajax_validate();

$result         = false;
$question_id      = post_value('question_id');
$answer_id    = post_value('answer_id');

if (is_numeric($question_id) && is_numeric($answer_id)) {

    $question     = new HuskyHuntQuestion(intval($question_id));
    $answer   = new HuskyHuntAnswer(intval($answer_id));
    $result     = $question->remove_answer($answer);
}

echo json_encode($result);
