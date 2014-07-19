<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php 

$ADMIN->ajax_validate();

$answer_id      = post_value('answer_id');
$body           = post_value('body');
$result         = false;

$answer = new HuskyHuntAnswer($answer_id);

if (!is_null($body))
    $answer->value = $body;

$result = $answer->save();

echo json_encode($result);

