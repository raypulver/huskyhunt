<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php
    
$ADMIN->ajax_validate();

$response = NULL;
$question_id = post_value('question_id');

$question = new HuskyHuntQuestion($question_id);
$response = $question->json_encode(); 

echo $response;
