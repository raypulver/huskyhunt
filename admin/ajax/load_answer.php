<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php
    
$ADMIN->ajax_validate();

$reslut = NULL;
$answer_id = post_value('answer_id');

$answer = new HuskyHuntAnswer($answer_id);
$reslut = $answer->json_encode(); 

echo $reslut;
