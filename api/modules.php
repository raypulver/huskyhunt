<?php require_once '../hh-config.php'; ?>
<?php 
  $huskyhunt = new HuskyHunt();
  if (!isset($_GET['id'])) {
    $incomplete_module_ids = $USER->postponed_quiz_modules();
    $incomplete_modules = array();
    foreach ($incomplete_module_ids as $id) {
      $module = new HuskyHuntModule($id);
      $incomplete_modules[] = array('id' => $module->get_id(), 'title' => $module->title);
    }
    $unshared_module_ids  = $USER->postponed_share_modules();
    $unshared_modules = array();
    foreach ($unshared_module_ids as $id) {
      $module = new HuskyHuntModule($id);
      $unshared_modules[] = array('id' => $module->get_id(), 'title' => $module->title);
    }
    echo json_encode(array('incomplete' => $incomplete_modules, 'unshared' => $unshared_modules));
  } else {
    $module = new HuskyHuntModule($_GET['id']);
    $info = array(
      'id' => $module->get_id(),
      'title' => $module->title,
      'body' => $module->body,
      'insight' => $module->insight,
      'questions' => array()
    );
    foreach ($module->questions as $question) {
      $qdata = array(
        'id' => $question->get_id(),
        'body' => $question->body,
        'answers' => array() 
      );
      foreach ($question->answers as $answer) {
        $qdata['answers'][] = array('id' => $answer->get_id(), 'value' => $answer->value);
      }
      $info['questions'][] = $qdata;
    }
    echo json_encode($info);
  }
?>
