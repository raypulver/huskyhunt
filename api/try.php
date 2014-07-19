<?php require_once '/www/hh-config.php'; ?>
<?php 
  $huskyhunt = new HuskyHunt();

  $postdata = file_get_contents('php://input');
  $request = json_decode($postdata);
  $question_id = $request->q; 
  $answer_id = $request->a;
  $retjson = array(
    'winner' => false,
    'feedback' => '',
  );
  $question = new HuskyHuntQuestion($question_id);
  if (is_numeric($answer_id)) {
    foreach ($question->get_correct() as $correct) {
      if ($correct == $answer_id) {
        $retjson['winner'] = true;
      } 
    }
  } else {
    foreach ($question->get_correct() as $correct) {
      $ans = new HuskyHuntAnswer($correct);
      $real_answer_stripped = preg_replace('/<[^>]+>/', '', $ans->value);
      $real_answer_stripped = preg_replace('/&nbsp;/', ' ', $real_answer_stripped);
      $real_answer_stripped = preg_replace('/&rsquo;/', '\'', $real_answer_stripped);
      $real_answer_stripped = preg_replace('/\\n/', '', $real_answer_stripped);
 
      if ($real_answer_stripped == $answer_id) {
        $retjson['winner'] = true;
      }
    }
  }
  echo json_encode($retjson);
?>
