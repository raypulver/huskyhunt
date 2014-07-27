<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php
    
$ADMIN->ajax_validate();

$response = NULL;
$question_id = post_value('question_id');

$question = new HuskyHuntQuestion($question_id);
$response = json_decode($question->json_encode()); 
$response->feedback = $question->feedback;
$response->ad_text = $question->ad_text;
$response = json_encode($response);

echo $response;
