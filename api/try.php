<?php require_once '../hh-config.php'; ?>
<?php 
  $huskyhunt = new HuskyHunt();
  $postdata = file_get_contents('php://input');
  $request = json_decode($postdata);
  $question_id = $request->q; 
  $answer_id = $request->a;
  $token = HuskyHunt::decode_token($request->token);
  $USER = new HuskyHuntUser($token->netid);
  $retjson = array(
    'winner' => false,
    'feedback' => '',
  );
  $answer_sql_fragment = '';
  $question = new HuskyHuntQuestion($question_id);
  $USER->initialize_grade(new HuskyHuntModule($question->get_module_id()));
  if (is_object($answer_id)) {
    $answers = array();
    foreach ($answer_id as $key => $val) {
      if($val) {
        $answer_sql_fragment .= $key . ' ';
      }
      $answers[$key] = $val;
    }
    $retjson['winner'] = true;
    foreach ($question->get_correct() as $correct) {
      if (!$answers[$correct]) {
        $retjson['winner'] = false;
      }
    }
  }
  elseif (is_numeric($answer_id)) {
    $answer_sql_fragment = $answer_id;
    foreach ($question->get_correct() as $correct) {
      if ($correct == $answer_id) {
        $retjson['winner'] = true;
      } 
    }
  } else {
    $answer_sql_fragment = $answer_id;
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
  if (!$retjson['winner']) {
    $retjson['feedback'] = $question->feedback;
  }
//  echo json_encode($retjson);

  $db = HuskyHuntDatabase::shared_database();
  $SQL = 'INSERT INTO attempts (user_id, question_id, answer, time, correct) VALUES (:user_id, :question_id, :answer, NOW(), :correct);';
  if ($stmt = $db->prepare($SQL)) {
    $stmt->bindParam(':user_id', $USER->user_id);
    $stmt->bindParam(':question_id', $question_id);
    $stmt->bindParam(':answer', $answer_sql_fragment);
    $stmt->bindParam(':correct', $retjson['winner']);
    $stmt->execute();
  }
  if ($retjson['winner'] && $question->is_last()) {
    $module = new HuskyHuntModule($question->get_module_id());
    $perfect_score = array();
    $qs = $module->get_question_ids();
    foreach ($qs as $q_id) {
      $perfect_score[$q_id] = true;
    }
    $USER->quiz_attempt($module, $perfect_score);
    $SQL = 'INSERT IGNORE INTO completed (user_id) VALUES (:user_id);';
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindParam(':user_id', $USER->user_id);
      $stmt->execute();
    }
    if ($module->is_scavenger_module()) {
      $SQL = 'UPDATE completed SET scavenger_hunt = scavenger_hunt + 1 WHERE user_id = :user_id;';
    } else {
      $SQL = 'UPDATE completed SET other = other + 1 WHERE user_id = :user_id;';
    }
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindParam(':user_id', $USER->user_id);
      $stmt->execute();
    }
    $USER = new HuskyHuntUser($USER->netid);
    if ($USER->score > 100) {
      if ($USER->insert_badge_by_id(1)) {
        $retjson['new_badge'] = true;
      }
    }
    if ($USER->scavenger_modules_completed() > 6) {
      if ($USER->insert_badge_by_id(2)) {
        $retjson['new_badge'] = true;
      }
    }
    if ($USER->other_modules_completed() > 6) {
      if ($USER->insert_badge_by_id(3)) {
        $retjson['new_badge'] = true;
      }
    }
    $new_rank = $USER->get_rank_remix();
    if ($new_rank < 26) {
      if ($USER->insert_badge_by_id(4)) {
        $retjson['new_badge'] = true;
      }
    }
    if ($new_rank < 11) {
      if ($USER->insert_badge_by_id(5)) {
        $retjson['new_badge'] = true;
      }
    }
    foreach (HuskyHuntContentArea::load_all() as $ca) {
      if ($USER->has_completed_content_area($ca)) {
        if ($USER->insert_badge_by_id($ca->badge->badge_id)) {
          $retjson['new_badge'] = true;
        }
      }
    }
  }
  HuskyHuntLog::log_last_attempt();
  echo json_encode($retjson);
?>
