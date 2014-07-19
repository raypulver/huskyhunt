<?php require_once '../hh-config.php'; ?>
<?php 

$huskyhunt = new HuskyHunt();

$module_id = session_value('MODULE_ID');

if (is_numeric($module_id)) 
    $module = new HuskyHuntModule($module_id);

#if (!is_null($USER)) 
#    $module = $USER->next_module();

if (is_null($module)) 
    die('No module, redirect');


$keys = array_keys($_POST);
$results = array();

foreach ($module->questions as $question) {
    $results[$question->get_id()] = false;
    if ($question->count_answers() == 0)   
        $results[$question->get_id()] = true;
}

foreach ($keys as $key) {
    
    list($question_id) = sscanf($key, 'Q%d');

    if (!is_numeric($question_id)) 
        continue;
    
    $question   = $module->get_question($question_id);
    $answers    = array();

    if (is_array($_POST[$key])) {
        foreach ($_POST[$key] as $value) {
            list($answer_id) = sscanf($value, 'A%d');
            $answers[] = $answer_id;
        }
    } else {
        list($answer_id) = sscanf($_POST[$key], 'A%d');
        if (!empty($answer_id)) {
            $answers[] = $answer_id;
        } else {
            $answers[] = $_POST[$key];    
        }
    }
  
    $results[$question_id] = $question->grade_answers($answers);

}

$USER->quiz_attempt($module, $results);

echo json_encode($results);

