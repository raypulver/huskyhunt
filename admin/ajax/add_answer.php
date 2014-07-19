<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php
    
$ADMIN->ajax_validate();

$result         = false;
$question_id    = post_value('question_id');
$answer_id      = post_value('answer_id');


if (is_numeric($question_id)) {

    $question = new HuskyHuntQuestion(intval($question_id));
    $answer = (is_numeric($answer_id)) ? new HuskyHuntAnswer(intval($answer_id)) : new HuskyHuntAnswer();
    $result = $question->add_answer($answer);
}

if ($result) 
    echo $answer->json_encode();
else
    echo json_encode($result);
