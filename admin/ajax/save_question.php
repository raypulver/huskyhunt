<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php 

$ADMIN->ajax_validate();

$question_id    = post_value('question_id');
$body           = post_value('body');
$correct        = post_value('correct');
$feedback	= post_value('feedback');
$ad_text = post_value('ad_text');
$result         = false;

$question = new HuskyHuntQuestion($question_id);

if (!is_null($body))
    $question->body = $body;
if (!is_null($feedback))
    $question->feedback = $feedback;
if (!is_null($ad_text))
    $question->ad_text = $ad_text;
$question->set_correct($correct);

$result = $question->save();

echo json_encode($result);

