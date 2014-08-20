<?php 

#require_once './hh-config.php';
#include_once 'vendor/firebase/php-jwt/Firebase/PHP-JWT/Authentication/JWT.php'

define('USER_ROLE_PLAYER', 0);
define('USER_ROLE_ADMIN', 1);

if (isset($_SERVER['REMOTE_USER'])) {
    $NETID = $_SERVER['REMOTE_USER'];
} else {
    $NETID = 'zzz00000';
}

define('NETID', $NETID);

class HuskyHuntLog {

  private static $filepath = '/var/log/huskyhunt.log';
  private static function strip($str) {
    $str = preg_replace('/<[^>]+>/', '', $str);
    $str = preg_replace('/&nbsp;/', ' ', $str);
    $str = preg_replace('/&rsquo;/', '\'', $str);
    $str = preg_replace('/&lsquo;/', '\'', $str);
    $str = preg_replace('/&rdquo;/', '"', $str);
    $str = preg_replace('/&ldquo;/', '"', $str);
    $str = preg_replace('/&mdash;/', '-', $str);
    $str = preg_replace('/&ndash;/', '-', $str);
    $str = preg_replace('/&shy;/', '-', $str);
    $str = preg_replace('/\\n/', '', $str);
    return $str;
  }
  private static function ordinal($num) {
    $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
    return (($num % 100) >= 11 && ($num % 100) <= 13) ? $num . 'th' : $num . $ends[$num % 10];
  }
  public static function log_login($netid, $was_successful) {
    if ($was_successful) {
      self::log_text('[' . date('Y-m-d H:i:s') . ']' . ' LOGIN');
    } else {
      self::log_text('[' . date('Y-m-d H:i:s') . ']' . ' UNSUCCESSFUL LOGIN');
    }
    self::log_text('NetID: ' . $netid);
    self::log_text('IP: ' . $_SERVER['REMOTE_ADDR']);
  }
  public static function log_last_registration() {
    $db = HuskyHuntDatabase::shared_database();
    $SQL = 'SELECT * FROM users WHERE date_joined = (SELECT MAX(date_joined) FROM users)';
    if ($stmt = $db->prepare($SQL)) {
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      self::log_text('[' . $result['date_joined'] . '] NEW USER #' . $result['user_id']);
      self::log_text('NetID: ' . $result['netid']);
      self::log_text('E-mail: ' . $result['contact']);
    }
  }
  public static function log_last_attempt() {
    $log_text = '';
    $db = HuskyHuntDatabase::shared_database();
    $SQL = 'SELECT * FROM attempts WHERE attempt_id = (SELECT MAX(attempt_id) FROM attempts)';
    if ($stmt = $db->prepare($SQL)) {
      $stmt->execute();
      if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $attempt_id = $result['attempt_id'];
        $user_id = $result['user_id'];
        $question_id = $result['question_id'];
        $answer = $result['answer'];
        $time = $result['time'];
        $correct = $result['correct'];
        $user = HuskyHuntUser::from_id($user_id);
        $question = new HuskyHuntQuestion($question_id);
        $module = new HuskyHuntModule($question->get_module_id());
        self::log_text('[' . $time . '] NEW ATTEMPT #' . $attempt_id);
        self::log_text('NetID: ' . $user->netid);
        self::log_text('Level: ' . $question->get_module_id());
        self::log_text('Question ID: ' . $question->get_id());
        self::log_text(self::ordinal($question->index) . " of " . count($module->questions));
        self::log_text('"' . self::strip($question->body) . '"');
        if ($question->is_check_all_that_apply()) {
          $answers = array();
          $answer_ids = explode(' ', $answer);
          array_pop($answer_ids);
          foreach ($answer_ids as $answer) {
            $answers[] = new HuskyHuntAnswer(intval($answer));
          }
          $correct_count = 0;
          foreach ($answers as $answer) {
            if ($answer->is_correct()) {
              $correct_count++;
            }
          }
          self::log_text($correct_count . "/" . count($answers) . " correct.");
          self::log_text("Answers:");
          foreach ($answers as $answer) {
            self::log_text('"' . self::strip($answer->value) . '"');
          }
        } elseif (preg_match('/[0-9]+/', $answer)) {
          $answer_object = new HuskyHuntAnswer(intval($answer));
          $correct_count = 0;
          if ($answer_object->is_correct())
            $correct_count = 1;
          self::log_text($correct_count . "/" . "1 correct.");
          self::log_text('Answer: "' . self::strip($answer_object->value) . '"');
        } else {
          $correct_count = 0;
          foreach ($question->get_correct() as $correct) {
            $ans = new HuskyHuntAnswer($correct);
            if ($answer == self::strip($ans->value)) {
              $correct_count++;
            }
          }
          self::log_text($correct_count . '/1 correct.');
          self::log_text('Answer: "' . $answer . '"');
        }
        self::log_text('Rank is now ' . $user->get_rank_remix());
        $fh = fopen(self::$filepath, 'a');
        fwrite($fh, $log_text);
        fclose($fh);
      }
    }
  }
  public static function log_text($str) {
    if ($fh = fopen(self::$filepath, 'a')) {
      fwrite($fh, $str . "\n");
    } else {
      return false;
    }
    fclose($fh);
    return true;
  }
}

class Set {
  private $arr = array();
  public static function add() {
    $args = func_get_args();
    $accum = array();
    foreach ($args as $arg) {
      $accum = array_merge($accum, $arg->arr);
    }
    return new self($accum);
  }
  public function __construct ($arr = NULL) {
    if ($arr) {
      if (is_array($arr)) {
        $this->arr = array_unique($arr);
      }
      else {
        $this->arr = array($arr);
      }
    }
  }
  public function push ($val) {
    if (!in_array($val, $this->arr)) {
      array_push($this->arr, $val);
      return true;
    }
    else {
      return false;
    }
  }
  public function size() {
    return count($this->arr);
  }
}

class HuskyHuntDatabase {

    private static $_instance = NULL;
    private $_database = NULL;

    private function __construct() {

        $pdo_connection_string = sprintf('mysql:host=%s;dbname=%s', MYSQL_HOST, MYSQL_DATABASE);
      
        try {
            $db = new PDO($pdo_connection_string, MYSQL_USER, MYSQL_PASS);
            #array(PDO::ATTR_PERSISTENT => true)
        } catch (PDOException $e) {
            print_r($e);
        }
        #$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);
        $this->_database = $db; 
    }   

    public static function shared_instance() {
      
        if (is_null(self::$_instance))
            self::$_instance = new HuskyHuntDatabase();

        return self::$_instance; 

    }

    public static function shared_database() {
    
        $instance = self::shared_instance();
        return $instance->_database;
    }

}


class HuskyHuntAdmin {

    public function __construct() {}

    public function player_emails() {

        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT contact FROM users WHERE contact > ""';

        if ($stmt = $db->prepare($SQL)) {
            
            $result = $stmt->execute();

            while ($result && ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
                
                echo $row['contact'] . "\r\n";
            }
               

        }

        return $result;    
    }
    public static function forgot_password($email) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT netid FROM users WHERE contact = :contact';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':contact', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $token = HuskyHunt::encode(array('exp' => time() + 86000, 'netid' => $result['netid']));
        $headers = "From: account@" . DOMAIN_NAME . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (mail($email, 'HuskyHunt Password Change Request', 'To reset your password, <a href="' . BASE_URL . '/forgot.php?token=' . $token . '&admin=1">click here</a>', $headers)) {
          return true;
        } else {
          return false;
        }
       
      }
    }
    public static function login($netid, $pass) {
      $user = new HuskyHuntUser($netid);
      if ($user->login($pass)) {
        if (intval($user->role) == 1) {
          $_SESSION['netid'] = $netid;
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    } 
    public static function logout() {
      unset($_SESSION['netid']);
    }
    private function _sql_validate() {
        
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT TRUE FROM users WHERE netid = :netid AND role = :role';
        $valid  = false;
        
        if ($stmt = $db->prepare($SQL)) {
            
            $stmt->bindValue(':netid',  $_SESSION['netid'],              PDO::PARAM_STR);
            $stmt->bindValue(':role',   USER_ROLE_ADMIN,    PDO::PARAM_INT);

            $db->beginTransaction();
            $stmt->execute();

            $valid = ($stmt->fetch(PDO::FETCH_NUM));

            $db->commit();
        
        }

        return $valid;
    }

    public function page_validate() {

        $valid = $this->_sql_validate();
        if (!$valid) {
            
            header('HTTP/1.1 401 Access Denied');
            header(sprintf('Location: %s/admin/401.php', BASE_URL));
            die();
        }

    }
    public function module_redirect_if_already_logged_in() {
      $valid = $this->_sql_validate();
      if ($valid) {
        header(sprintf('Location: /admin/list_modules.php'));
        die();
      }
    }
    public function ajax_validate() {

        $valid = $this->_sql_validate();

        if (!$valid) {
            die('Access Denied');
        }
    }


    public function purge_dangling_questions() {
        
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'DELETE FROM questions WHERE question_id NOT IN (SELECT question_id FROM map_mq)';

        if ($stmt = $db->prepare($SQL)) {
            $stmt->execute();
        }

    }

    public function get_games() {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT game_id, title FROM games;';
        $games  = Array();

        if ($stmt = $db->prepare($SQL)) {
            
            $stmt->execute();

            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $games[] = $result;
            }
        }

        return $games;
    }

    public function get_modules() {
        
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT module_id, title FROM modules ORDER BY title ASC;';
        $modules = array();

        if ($stmt = $db->prepare($SQL)) {

            $stmt->execute();
        
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $modules[] = $result;
            }

        }
        
        return $modules;

    }
    
    public function get_non_empty_title_modules() {
        
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT module_id, title FROM modules ORDER BY title ASC;';
        $modules = array();

        if ($stmt = $db->prepare($SQL)) {

            $stmt->execute();
        
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
              if (!is_null($result['title']) && !empty($result['title']))
                $modules[] = $result;
            }

        }
        
        return $modules;

    }
    
    public function get_questions() {
        
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT question_id, body FROM questions;';
        $questions = array();

        if ($stmt = $db->prepare($SQL)) {

            $stmt->execute();
        
            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $questions[] = $result;
            }

        }
        
        return $questions;

    }




}




/*

    Flow:

        1) Pick A Module To Load: 
            utilizes: timeline, modules
        2) Load A Module:
            3) Load the module-question map (map_mq)
            4) Load the questions listed in the map_mq
                5) Load the question-answer map (map_qa)
                6) Load the answers listed in the map_qa
                7) Load the answer key for the given question_id

        Module->
            Question 1
                Answer 1
                Answer 2
                ... 
                Answer N
                Correct Answer: 1, 2
            Question 2
                Answer 1
                Answer 2
                ... 
                Answer N
                Correct Answer: 2
            ...
            Question N
                Answer 1
                Answer 2
                ... 
                Answer N
                Correct Answer: 1
   

        Create a module: 
            
            Type out the module name and text
            add questions

            create a question: 
                provide text
                add answers 
                select the correct answers

                create an answer:
                    provide text


*/


class HuskyHunt {

    public static $jwt_key = JWT_KEY;
    public static function decode_token($token) {
      return JWT::decode($token, self::$jwt_key);
    }
    public static function encode($arr) {
     return JWT::encode($arr, self::$jwt_key);
    }
    public static function get_token_object() {
      $headers = getallheaders();
      $token = substr($headers['Authorization'], 7);
      return self::decode_token($token);
    }
    public static function status_code($code) {
      if ($code == 401) {
        header('HTTP/1.1 401 Unauthorized', 401);
      } else if ($code == 400) {
        header('HTTP/1.1 400 Bad Request');
      } else if ($code == 403) {
        header('HTTP/1.1 403 Forbidden');
      } else if ($code == 201) {
        header('HTTP/1.1 201 Created');
      }
    }
    public static function count_attempts() {
      $correct = 0;
      $total = 0;
      foreach (HuskyHuntUser::select_all() as $user) {
        $user->load_attempts();
        $total += count($user->attempts);
        foreach ($user->attempts as $attempt) {
          if ($attempt->was_correct) {
            $correct++;
          }
        }
      }
      return array($correct, $total);
    }
    public static function badges_enabled() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT value FROM options WHERE `key` = \'enable_badges\'';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row['value']) {
          return true;
        } else {
          return false;
        }
      }
    }
    public static function set_badges($bool) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'UPDATE options SET value = :value WHERE `key` = \'enable_badges\'';
      if ($stmt = $db->prepare($SQL)) {
        if ($bool) {
          $val = 1;
        } else {
          $val = 0;
        }
        $stmt->bindParam(':value', $val);
        $stmt->execute();
      }
    }
    public static function get_statistic_end_date() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT date FROM dates WHERE date_id = \'end_date\'';
      if ($stmt = $db->prepare($SQL)) {
        if ($stmt->execute()) {
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          return strtotime($row['date']);
        }
        return false;
      }
      return false;
    }
    public static function set_statistic_end_date($timestamp) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'UPDATE dates SET date = FROM_UNIXTIME(:timestamp) WHERE date_id = \'end_date\'';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':timestamp', $timestamp);
        if ($stmt->execute()) {
          return true;
        }
        return false;
      }
      return false;
    }
    public function has_begun() {
        return true;
    }
    public function has_ended() {
        return false;
    }
    
    public function start_time() {
        return 1379030400;
    }
    public function end_time() {
      return 1407158356;
    }

    public function get_user() {
        
        return new HuskyHuntUser(NETID);

    }
   
    public function create_user() {
    }

    public function daily_module() {

        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT * FROM (SELECT module_id FROM (modules LEFT JOIN timeline USING (module_id)) WHERE start < NOW() AND stop > NOW() AND bonus = TRUE ORDER BY start, module_id) AS t WHERE NOT EXISTS (SELECT * FROM grades WHERE module_id = t.module_id AND user_id = 4 AND complete = TRUE) LIMIT 1;';

        $module_id = NULL;

        if ($stmt = $db->prepare($SQL)) {
            
            $result = $stmt->execute();
            
            if ($result) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $module_id = $row['module_id'];
            }
        }

        return $module_id;
    }
    public function render_scores_table() {

        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT netid, real_points, score FROM (SELECT user_id, SUM(((points * complete) + (social_points * shared))) as real_points  FROM modules LEFT JOIN grades USING (module_id) GROUP BY user_id ORDER BY points DESC) AS calculated LEFT JOIN users USING (user_id) ORDER BY real_points DESC';
        #$SQL = 'SELECT netid, score FROM users ORDER BY score DESC';
        $TR_FORMAT_STRING = '<tr class="%s"><td>%d</td><td>%s</td><td>%d</td></tr>';
        $rank = 0;
        $previous_score = PHP_INT_MAX;

        echo '
        <table id="hh-scores-table" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>NetID</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
        ';

        if ($stmt = $db->prepare($SQL)) {

            $result = $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $netid = $row['netid'];
                #$score = $row['score'];
                $score = $row['real_points'];

                if ($score < $previous_score)
                    $rank ++;

                $css = ($netid == NETID) ? 'success' : '';

                echo sprintf($TR_FORMAT_STRING, $css, $rank, $netid, $score);

                $previous_score = $score;
            }
        }

        echo '
            </tbody>
        </table>
        ';

    }
    public function scores_json() {

        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT netid, real_points, score FROM (SELECT user_id, SUM(((points * complete) + (social_points * shared))) as real_points  FROM modules LEFT JOIN grades USING (module_id) GROUP BY user_id ORDER BY points DESC) AS calculated LEFT JOIN users USING (user_id) ORDER BY real_points DESC';
        $rank = 0; 
        $previous_score = PHP_INT_MAX; 
        $rows = array();
        if ($stmt = $db->prepare($SQL)) {
            
            $result = $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                
                $netid = $row['netid']; 
                $score = $row['real_points']; 
                if (!is_null($netid) && !is_null($score)) { 
                  if ($score < $previous_score) 
                    $rank ++; 
                  $rows[] = array('rank' => $rank, 'netid' => $netid, 'points' => $score);
                  $previous_score = $score;
                }
            }
        }
        echo json_encode($rows);
    }
    public function scores_json_remix() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT netid, alias, score FROM users WHERE netid != \'\' ORDER BY score DESC';
      $rank = 0;
      $previous_score = PHP_INT_MAX;
      $rows = array();
      if ($stmt = $db->prepare($SQL)) {
        $result = $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $name = $row['netid'];
          if (!is_null($row['alias']) && strlen($row['alias']) > 1) {
            $name = $row['alias'];
          }
          $score = $row['score'];
          if (!is_null($name) && !is_null($score)) {
            if ($score < $previous_score)
              $rank ++;
            $rows[] = array('rank' => $rank, 'netid' => $name, 'points' => $score);
            $previous_score = $score;
          }
        }
      }
      echo json_encode($rows);
    }
    public function render_knowledge_base() {

        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT DISTINCT(module_id), title, body FROM modules LEFT JOIN timeline USING (module_id) WHERE (start < NOW() AND knowledge_base = TRUE) ORDER BY start ASC;'; 
        $MODULE_FORMAT_STRING = '<div class="col-md-8 col-md-offset-2"><div class="well"><h3>%s</h3><hr /><div>%s</div></div></div>'; 

        echo '
        ';
    
        if ($stmt = $db->prepare($SQL)) {
            
            $result = $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          
                $title = $row['title'];
                $body  = $row['body'];

                echo sprintf($MODULE_FORMAT_STRING, $title, $body);
            }
        }

        echo '  
        ';

    }
    

}


class HuskyHuntAnswer {

    private $answer_id      = NULL;
    public  $value          = ''; 
    public  $correct        = NULL;    

    public function __construct($answer_id = NULL) {
        
        if (!is_null($answer_id))
            $this->load($answer_id);

    }

    public function get_id() {
        return $this->answer_id;
    }

    public function json_encode() {
        
        $data = array(
            'answer_id' =>$this->answer_id,
            'body'      =>$this->value
        );
        
        return json_encode($data);
    }


    public function load($answer_id) {
    
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT * FROM answers WHERE answer_id=:answer_id';
        $result = false;

        if (is_numeric($answer_id) && ($stmt = $db->prepare($SQL))) {
            
            $answer_id = intval($answer_id);
    
            $stmt->bindParam(':answer_id', $answer_id);
            $stmt->execute();
        
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                
                $this->value = $result['answer'];
                $this->answer_id  = $answer_id;
            }

        }
        

    }

    public function is_correct() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT answer_id FROM answer_key WHERE answer_id = :answer_id;';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':answer_id', $this->answer_id);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    public function save() {
        
        $db = HuskyHuntDatabase::shared_database();

        $INSERT_SQL = 'INSERT INTO answers (answer) VALUES (:answer)';
        $UPDATE_SQL = 'UPDATE answers SET answer=:answer WHERE answer_id=:answer_id';
        $SQL        = (is_null($this->answer_id)) ? $INSERT_SQL : $UPDATE_SQL; 
        
        $result     = false;

        if ($stmt = $db->prepare($SQL)) {

            if ($SQL == $UPDATE_SQL)
                $stmt->bindParam(':answer_id', $this->answer_id);
            $stmt->bindValue(':answer', $this->value, PDO::PARAM_STR);
           
            $db->beginTransaction();
            $stmt->execute(); 
           
            if ($SQL == $INSERT_SQL) { 
                $result = $db->lastInsertId();
                if ($result != 0) 
                    $this->answer_id = $result;
            } else {
                $result = ($stmt->rowCount() === 1);
            }

            $db->commit();
            
        }

        return $result; 

    }


}




class HuskyHuntQuestion {

    private $question_id        = NULL;
    public  $body       = ''; 
    public  $answers    = array();
    public $feedback	= '';
    public $index = NULL;
    private $answer_ids = array();
    private $correct    = array();
    public $ad_text = '';

    public function __construct($question_id = NULL) {
        
        if (!is_null($question_id))
            $this->load($question_id);
    }
    public function is_check_all_that_apply() {
      return count($this->correct) > 1;
    }
    public function is_last() { 
      $db     = HuskyHuntDatabase::shared_database();
      $SQL1    = 'SELECT module_id FROM map_mq WHERE question_id = :question_id;';
      $SQL2 = 'SELECT MAX(question_id) AS max_q FROM map_mq WHERE module_id = :module_id;';
      $questions = array();
      if ($stmt = $db->prepare($SQL1)) {
        $stmt->bindParam(':question_id', $this->question_id);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $module = $result['module_id'];
        }
      }
      if ($stmt = $db->prepare($SQL2)) {
        $stmt->bindParam(':module_id', $module);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $last_question = $result['max_q'];
        }
      }
      return $last_question == $this->question_id;
    }
    public function get_module_id() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT module_id FROM map_mq WHERE question_id = :question_id;';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':question_id', $this->question_id);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $module = $result['module_id'];
        }
      }
      return $module;
    }
    public function count_answers() {
        return count($this->answers);
    }

    public function json_encode() {

        $answers = array();
        
        foreach ($this->answers as $answer) {
            $answers[] = json_decode($answer->json_encode()); 
        }
        
        $data = array(
            'question_id'   => $this->question_id,
            'body'          => $this->body,
            'answers'       => $answers,
            'correct'       => $this->correct
        );

        return json_encode($data);
    }
    public function get_correct() {
      return $this->correct;
    }

    public function get_id() {
        return $this->question_id;
    }
   
    public function grade_answers($answers) {
      
        $result = true; 

        $result &= (count($this->correct) == count($answers));
       
        if ($result && (count($this->answers) == 1)) {
            
            $true_answer = strtolower(trim($this->answers[0]->value));
            $true_answer = strip_tags($true_answer);
            $given_answer = strtolower(trim($answers[0]));

            $result &= ($true_answer == $given_answer);

        } elseif ( (count($this->answers) > 1)) {
            $delta = array_diff($answers, $this->correct);
        } 
        $result &= empty($delta);

        return $result; 
    }

    public function render_answers() {
        
        $NAME           = 'Q%d';
        $VALUE          = "A%d";
        $TEXT_INPUT     = '<input type="text" name="%s" class="form-control" placeholder="fill in answer" />';
        $CHECKBOX_INPUT = '<div class="checkbox col-md-12"><label><input type="checkbox" name="%s[]" value="%s"><div class="row quiz-row" style="margin: 0;">%s</div></label></div>';
        $RADIO_INPUT    = '<div class="radio col-md-12"><label><input type="radio" name="%s" value="%s"><div class="row quiz-row" style="margin: 0;">%s</div></label></div>';

        echo '<div class="well">';

        if (count($this->answers) == 1) {
            echo sprintf($TEXT_INPUT,
                            sprintf($NAME, $this->question_id));
        } else {
           
            foreach ($this->answers as $answer) {

                if (count($this->correct) > 1) {
                    echo sprintf($CHECKBOX_INPUT, 
                                    sprintf($NAME, $this->question_id),  
                                    sprintf($VALUE, $answer->get_id()),
                                    $answer->value);
                } else {
                    echo sprintf($RADIO_INPUT, 
                                    sprintf($NAME, $this->question_id), 
                                    sprintf($VALUE, $answer->get_id()),
                                    $answer->value);

                }
            }
        }
        
        echo '</div>';
    }

    public function add_answer($answer) {
    
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'INSERT INTO map_qa (question_id, answer_id) VALUES (:question_id, :answer_id)';
        $result = true;

        # check to see if we are actually adding a answer
        if ($answer instanceof HuskyHuntAnswer) {

            # this should ensure that the answer has an id. 
            $answer->save();

            if (!is_null($this->question_id) && !is_null($answer->get_id())) {

                if ($stmt = $db->prepare($SQL)) {
                
                    $stmt->bindParam(':question_id', $this->question_id);
                    $stmt->bindParam(':answer_id', $answer->get_id());

                    $stmt->execute();
                
                    $result = ($stmt->errorCode() == 0);
    
                }
            }
        }

        return $result;

    }

    function remove_answer($answer) {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'DELETE FROM map_qa WHERE question_id = :question_id AND answer_id = :answer_id LIMIT 1'; 
        $result = true;

        # check to see if we are actually adding a answer
        if ($answer instanceof HuskyHuntAnswer) {

            # this should ensure that the answer has an id. 
            $answer->save();

            if (!is_null($this->question_id) && !is_null($answer->get_id())) {

                if ($stmt = $db->prepare($SQL)) {
                
                    $stmt->bindParam(':question_id', $this->question_id);
                    $stmt->bindParam(':answer_id', $answer->get_id());

                    $stmt->execute();
                
                    $result = ($stmt->rowCount() == 1);
                
                    #if ($result) 
                    #    HuskyHuntAdmin::purge_dangling_answers();
        
                }
            }
        }

        return $result;
    }

    public function set_correct($correct) {

        $valid_correct = Array();

        foreach ($this->answers as $answer) {
            
            if (in_array($answer->get_id(), $correct)) {
                $valid_correct[] = $answer->get_id();
            }

        }

        $this->correct = $valid_correct;

        $this->save();
        $this->delete_correct();

        foreach($this->correct as $answer_id) {
            $this->insert_correct($answer_id);
        }

    }

    private function delete_correct() {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'DELETE FROM answer_key WHERE question_id = :question_id';
        $result = false;
    
        if ($stmt = $db->prepare($SQL)) {
            
            $stmt->bindParam(':question_id', $this->question_id);
            $stmt->execute();

            $result = ($stmt->errorCode() == 0);
        }

        return $result;


    }

    private function insert_correct($answer_id) {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'INSERT INTO answer_key (question_id, answer_id) VALUES (:question_id, :answer_id)';
        $result = false;
    
        if ($stmt = $db->prepare($SQL)) {
            
            $stmt->bindParam(':question_id', $this->question_id);
            $stmt->bindParam(':answer_id', $answer_id);
            
            $stmt->execute();

            $result = ($stmt->errorCode() == 0);
        }

        return $result;

    }
    

    public function load($question_id) {
    
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT * FROM questions WHERE question_id=:question_id';

        if (is_numeric($question_id) && ($stmt = $db->prepare($SQL))) {

            $question_id = intval($question_id);
            
            $stmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
            $stmt->execute();
        
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
              
                $this->body = $result['body'];
                $this->feedback = $result['feedback'];
                $this->question_id  = $result['question_id'];
                $this->ad_text = $result['ad_text'];
                $this->load_answers();
                $this->load_answer_key();
                $this->load_index();
            }
        }
    }
    private function load_index() {
      $index = 0;
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT * FROM map_mq WHERE module_id = (SELECT module_id FROM map_mq WHERE question_id = :question_id)';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':question_id', $this->question_id);
        if ($result = $stmt->execute()) {
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $index++;
            if ($row['question_id'] == $this->question_id) {
              $this->index = $index;
              break;
            }
          }
        }
      }
    }
    public function load_answer_key() {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT answer_id FROM answer_key WHERE question_id=:question_id';

        if (is_numeric($this->question_id) && ($stmt = $db->prepare($SQL))) {

            $stmt->bindParam(':question_id', $this->question_id);
            $stmt->execute();

            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->correct[] = $result['answer_id'];
            }
        }
    }

    public function load_answers() {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT answer_id FROM map_qa WHERE question_id=:question_id';

        if (is_numeric($this->question_id) && ($stmt = $db->prepare($SQL))) {

            $stmt->bindParam(':question_id', $this->question_id);
            $stmt->execute();

            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->answer_ids[] = $result['answer_id'];
                $this->answers[] = new HuskyHuntAnswer($result['answer_id']);
            }
        }
    }

    public function save() {
        
        $db = HuskyHuntDatabase::shared_database();

        $INSERT_SQL = 'INSERT INTO questions (body, feedback, ad_text) VALUES (:body, :feedback, :ad_text)';
        $UPDATE_SQL = 'UPDATE questions SET body=:body, feedback=:feedback, ad_text=:ad_text WHERE question_id=:question_id';
        $SQL        = (is_null($this->question_id)) ? $INSERT_SQL : $UPDATE_SQL; 
        
        $result     = false;

        if ($stmt = $db->prepare($SQL)) {
            if ($SQL == $UPDATE_SQL)
                $stmt->bindValue(':question_id', $this->question_id, PDO::PARAM_INT);
            $stmt->bindValue(':body', $this->body, PDO::PARAM_STR);
            $stmt->bindValue(':feedback', $this->feedback, PDO::PARAM_STR);
            $stmt->bindValue(':ad_text', $this->ad_text, PDO::PARAM_STR);
            $db->beginTransaction();
            $stmt->execute(); 
            
            if ($SQL == $INSERT_SQL) { 
                $result = $db->lastInsertId();
                if ($result != 0) 
                    $this->question_id = $result;
            } else {
                $result = ($stmt->errorCode() == 0);
            }

            $db->commit();
            
        }

        return $result; 

    }

}


class HuskyHuntModule {

    private $module_id      = NULL; 
    public  $title          = '';
    public  $body           = '';
    public  $insight        = '';
    public  $vendor         = '';
    public  $points         = 0;
    public  $social_points  = 0;
    #public  $game_id        = 0;
    public  $decay          = true;
    public  $postponable    = false;
    public  $bonus          = false; 
    public  $knowledge_base = true; 

    public  $questions      = array();
    private $question_ids   = array();
    private $timeline       = array();

    public function __construct($module_id = NULL) {

        if (!is_null($module_id))
            $this->load($module_id);

    }
    function get_question_ids() {
      return $this->question_ids;
    }
    function get_id() {
      return $this->module_id;
    }
    function current_points() {
      $score = $this->points;
      $seconds_passed = time() - $this->timeline[0]['start'];
      return $this->decay ? ceil((float) $score / 3 + ((float) 2 / 3) * $score * pow((float) 1 / 4, (float) $seconds_passed / 86400)) : $this->points;
    }
    function is_scavenger_module() {
      if (count($this->questions) == 1) {
        foreach ($this->questions as $question) {
          if (count($question->answers) == 1) {
            return true;
          } else {
            return false;
          }
        }
      } else {
        return false;
      }
    }
    function get_question($question_id) {
    
        $question = NULL;
         
        if (array_key_exists($question_id, $this->questions)) 
            $question = $this->questions[$question_id];

        return $question;
    }

    function remove_question($question) {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'DELETE FROM map_mq WHERE module_id = :module_id AND question_id = :question_id LIMIT 1'; 
        $result = true;

        # check to see if we are actually adding a question
        if ($question instanceof HuskyHuntQuestion) {

            # this should ensure that the question has an id. 
            $question->save();

            if (!is_null($this->module_id) && !is_null($question->get_id())) {

                if ($stmt = $db->prepare($SQL)) {
                
                    $stmt->bindParam(':module_id', $this->module_id);
                    $stmt->bindParam(':question_id', $question->get_id());

                    $stmt->execute();
                
                    $result = ($stmt->rowCount() == 1);
                
                    if ($result) 
                        HuskyHuntAdmin::purge_dangling_questions();
        
                }
            }
        }

        return $result;
    }

    function add_question($question) {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'INSERT INTO `map_mq` (module_id, question_id) VALUES (:module_id, :question_id)';
        $result = true;

        # check to see if we are actually adding a question
        if ($question instanceof HuskyHuntQuestion) {

            # this should ensure that the question has an id. 
            $question->save();

            if (!is_null($this->module_id) && !is_null($question->get_id())) {

                if ($stmt = $db->prepare($SQL)) {
                
                    $stmt->bindParam(':module_id', $this->module_id);
                    $stmt->bindParam(':question_id', $question->get_id());

                    $stmt->execute();
                
                    $result = ($stmt->errorCode() == 0);
    
                }
            }
        }

        return $result;
    }

    function get_timeline() {
        return $this->timeline;
    }

    function add_timeline($start, $stop) {
        
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'INSERT INTO `timeline` (module_id, start, stop) VALUES (:module_id, :start, :stop)';
        $result = true;

        if (is_numeric($this->module_id) && ($stmt = $db->prepare($SQL))) {

            $stmt->bindParam(':module_id', $this->module_id, PDO::PARAM_INT);
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':stop', $stop, PDO::PARAM_STR);
            
            $db->beginTransaction();
            $stmt->execute();
            $result = $db->lastInsertId();

            $db->commit();
           
        }

        return $result;
    }


    function remove_timeline($timeline_id) {
        
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'DELETE FROM `timeline` WHERE timeline_id = :timeline_id AND module_id = :module_id LIMIT 1';
        $result = true;

        if (is_numeric($this->module_id) && ($stmt = $db->prepare($SQL))) {

            $stmt->bindParam(':module_id', $this->module_id, PDO::PARAM_INT);
            $stmt->bindParam(':timeline_id', $timeline_id, PDO::PARAM_INT);
            
            $db->beginTransaction();
            $result = $stmt->execute();
            $result &= $stmt->rowCount();

            $db->commit();
           
        }

        return $result;
    }

    
    function load($module_id) {
    
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT module_id, title, body, insight, points, social_points, decay, postponable, bonus, knowledge_base, vendor FROM modules WHERE module_id=:module_id';

        if (is_numeric($module_id) && ($stmt = $db->prepare($SQL))) {

            $module_id = intval($module_id);
     
            $stmt->bindParam(':module_id', $module_id);

            $stmt->execute();

            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            
                $this->module_id    = $result['module_id'];
                #$this->game_id      = $result['game_id'];
                $this->title        = $result['title'];
                $this->body         = $result['body'];
                $this->insight      = $result['insight'];
                $this->points      = $result['points'];
                $this->social_points      = $result['social_points'];
                $this->decay      = $result['decay'];
                $this->postponable  = $result['postponable'];
                $this->bonus        = $result['bonus'];
                $this->vendor = $result['vendor'];
                $this->knowledge_base        = $result['knowledge_base'];
                $this->load_questions();
                $this->load_timeline();
            }
        }
    }

    private function load_timeline() {
 
        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT timeline_id, start, stop FROM timeline WHERE module_id = :module_id ORDER BY start, timeline_id ASC';

        if ($stmt = $db->prepare($SQL)) {

            $stmt->bindParam(':module_id', $this->module_id, PDO::PARAM_INT);

            $stmt->execute();

            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->timeline[] = $result;
            }
        }       

    }

    private function load_questions() {

        $db     = HuskyHuntDatabase::shared_database();
        $SQL    = 'SELECT question_id FROM map_mq WHERE module_id=:module_id';

        if (is_numeric($this->module_id) && ($stmt = $db->prepare($SQL))) {

            $stmt->bindParam(':module_id', $this->module_id);
            $stmt->execute();

            while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $result['question_id'];

                $this->question_ids[] = $id;
                $this->questions[$id] = new HuskyHuntQuestion($id);
            }
        }
    }

    function save() {

        $db         = HuskyHuntDatabase::shared_database();
        #$INSERT_SQL = 'INSERT INTO modules (title, body, game_id) VALUES (:title, :body, :game_id)';
        $INSERT_SQL = 'INSERT INTO modules (title, body, insight, points, social_points, decay, postponable, bonus, knowledge_base, vendor) VALUES (:title, :body, :insight, :points, :social_points, :decay, :postponable, :bonus, :knowledge_base, :vendor)';
        #$UPDATE_SQL = 'UPDATE modules SET title=:title, body=:body, game_id=:game_id WHERE module_id=:module_id';
        $UPDATE_SQL = 'UPDATE modules SET title=:title, body=:body, insight = :insight, points = :points, social_points = :social_points, decay = :decay, postponable = :postponable, bonus = :bonus, knowledge_base = :knowledge_base, vendor = :vendor WHERE module_id=:module_id';
        $SQL        = (is_null($this->module_id)) ? $INSERT_SQL : $UPDATE_SQL;
        $result     = false;

        if ($stmt = $db->prepare($SQL)) {

            if ($SQL == $UPDATE_SQL)
                $stmt->bindParam(':module_id', $this->module_id);

            $stmt->bindValue(':title',          $this->title,       PDO::PARAM_STR);
            $stmt->bindValue(':body',           $this->body,        PDO::PARAM_STR);
            $stmt->bindValue(':insight',        $this->insight,     PDO::PARAM_STR);
            $stmt->bindValue(':points',        $this->points,     PDO::PARAM_INT);
            $stmt->bindValue(':social_points',        $this->social_points,     PDO::PARAM_INT);
            $stmt->bindValue(':decay', $this->decay, PDO::PARAM_BOOL);
            $stmt->bindValue(':postponable',    $this->postponable, PDO::PARAM_BOOL);
            $stmt->bindValue(':bonus',    $this->bonus, PDO::PARAM_BOOL);
            $stmt->bindValue(':knowledge_base',    $this->knowledge_base, PDO::PARAM_BOOL);
            $stmt->bindValue(':vendor', $this->vendor, PDO::PARAM_STR);
            //$stmt->bindValue(':game_id',    $this->game_id, PDO::PARAM_INT);
             
            $db->beginTransaction();
            $result = $stmt->execute(); 

            if (!$result && HH_DEBUG) {
                die($stmt->errorCode());
            }

            if ($SQL == $INSERT_SQL) { 
                $result = $db->lastInsertId();
                if ($result != 0) 
                    $this->module_id = $result;
                //else
                //die("Error: " . $stmt->errorCode());
            } else {
                $result = ($stmt->errorCode() == 0);
            }

            $db->commit();
        }

        return $result; 
    }

}

class HuskyHuntGame {

    private $game_id;
    private $title; 

    function load() {}
    function save() {}
    
}
class HuskyHuntAttempt {
  public $attempt_id = NULL;
  public $question = NULL;
  public $module = NULL;
  public $answers = array();
  public $time = NULL;
  public $was_correct = false;

  function __construct($attempt_id = NULL) {
    $db = HuskyHuntDatabase::shared_database();
    $SQL = 'SELECT * FROM attempts WHERE attempt_id = :attempt_id';
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindValue(':attempt_id', $attempt_id);
      $result = $stmt->execute();
      if ($result) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->attempt_id = $attempt_id;
        $this->time = strtotime($row['time']);
        $this->was_correct = $row['correct'];
        $this->question = new HuskyHuntQuestion($row['question_id']);
        $this->module = new HuskyHuntModule($this->question->get_module_id());
        $answers = $row['answer'];
        if (preg_match('/[0-9]+\s[0-9].*/', $answers)) {
          $answers = explode(' ', $answers);
          $answer_objects = array();
          for ($i = 0; $i < count($answers) - 1; $i++) {
            $answer_objects[] = new HuskyHuntAnswer(intval($answers[$i]));
          }
          $this->answers = $answer_objects;
        } elseif (preg_match('/[0-9]+/', $answers)) {
          $this->answers[] = new HuskyHuntAnswer(intval($answers));
        } else {
          $this->answers[] = $answers;
        }
      }
    }
  }
}
class HuskyHuntContentArea {
  public $area_id = NULL;
  public $name = NULL;
  public $badge = NULL;
  public $module_ids = array();
  function __construct($area_id = NULL) {
    if (!is_null($area_id)) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT * FROM content_areas WHERE area_id = :area_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':area_id', $area_id);
        if ($result = $stmt->execute()) {
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $this->badge = new HuskyHuntBadge($row['badge_id']);
          $this->name = $row['name'];
          $this->area_id = $area_id;
          $this->load_modules();
        }
      }
    }
  }
  public static function delete_by_id($id) {
    $db = HuskyHuntDatabase::shared_database();
    $SQL = 'DELETE FROM content_areas WHERE area_id = :area_id';
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindParam(':area_id', $id);
      $stmt->execute();
    }
    $SQL = 'DELETE FROM map_cam WHERE area_id = :area_id';
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindParam(':area_id', $id);
      $stmt->execute();
    }
  }
  public static function load_all() {
    $db = HuskyHuntDatabase::shared_database();
    $SQL = 'SELECT area_id FROM content_areas';
    $ret = array();
    if ($stmt = $db->prepare($SQL)) {
      if ($result = $stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $ca = new HuskyHuntContentArea($row['area_id']);
          $ret[] = $ca;
        }
        return $ret;
      }
      return false;
    }
    return false;
  }
  function load_modules() {
    $db = HuskyHuntDatabase::shared_database();
    $SQL = 'SELECT * FROM map_cam WHERE area_id = :area_id';
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindParam(':area_id', $this->area_id);
      if ($result = $stmt->execute()) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $this->module_ids[] = $row['module_id'];
        }
      }
    }
  }
  public function save() {
    $db = HuskyHuntDatabase::shared_database();
    $INSERT_SQL = 'INSERT INTO content_areas (name, badge_id) VALUES (:name, :badge_id)';
    $UPDATE_SQL = 'UPDATE content_areas SET name = :name, badge_id = :badge_id WHERE area_id = :area_id';
    $SQL = is_null($this->area_id) ? $INSERT_SQL : $UPDATE_SQL;
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindParam(':name', $this->name);
      $stmt->bindParam(':badge_id', $this->badge->badge_id);
      if (!is_null($this->area_id))
        $stmt->bindParam(':area_id', $this->area_id);
      $db->beginTransaction();
      if ($result = $stmt->execute()) {
        if ($SQL == $INSERT_SQL) {
          $this->area_id = $db->lastInsertId();
        }
        $db->commit();
        $SQL = 'DELETE FROM map_cam WHERE area_id = :area_id';
        if ($stmt = $db->prepare($SQL)) {
          $stmt->bindParam(':area_id', $this->area_id);
          $stmt->execute();
          foreach ($this->module_ids as $module) {
            $SQL = 'INSERT INTO map_cam VALUES (:area_id, :module_id)';
            if ($stmt = $db->prepare($SQL)) {
              $stmt->bindParam(':area_id', $this->area_id);
              $stmt->bindParam(':module_id', $module);
              $stmt->execute();
            }
          }
        }
      }
    }
  }
}
class HuskyHuntBadge {
  public $badge_id = NULL;
  public $name = NULL;
  public $image = NULL;
  public $unearned_desc = NULL;
  public $earned_desc = NULL;
  
  function __construct($badge_id = NULL) {
    if (!is_null($badge_id)) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT * FROM badges WHERE badge_id = :badge_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindValue(':badge_id', $badge_id);
        $result = $stmt->execute();
        if ($result) {
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $this->badge_id = $badge_id;
          $this->name = $row['name'];
          $this->image = $row['image'];
          $this->unearned_desc = $row['unearned_desc'];
          $this->earned_desc = $row['earned_desc'];
        }
      }
    }
  }
  public function save() {
    $db = HuskyHuntDatabase::shared_database();
    $INSERT_SQL = 'INSERT INTO badges (name, image, unearned_desc, earned_desc) VALUES (:name, :image, :unearned_desc, :earned_desc)';
    $UPDATE_SQL = 'UPDATE badges SET name = :name, image = :image, unearned_desc = :unearned_desc, earned_desc = :earned_desc WHERE badge_id = :badge_id';
    $SQL = is_null($this->badge_id) ? $INSERT_SQL : $UPDATE_SQL;
    if ($stmt = $db->prepare($SQL)) {
      $stmt->bindParam(':name', $this->name);
      $stmt->bindParam(':image', $this->image);
      $stmt->bindParam(':unearned_desc', $this->unearned_desc);
      $stmt->bindParam(':earned_desc', $this->earned_desc);
      if ($SQL == $UPDATE_SQL)
        $stmt->bindParam(':badge_id', $this->badge_id);
      $db->beginTransaction();
      if ($result = $stmt->execute()) {
        if ($SQL == $INSERT_SQL)
          $this->badge_id = $db->lastInsertId();
        $db->commit();
      }
    }
  } 
}
class HuskyHuntUser {

    public $user_id     = NULL;
    public $netid   = NULL;
    public $alias = NULL;
    public $date_joined = NULL;
    public $role    = 0;
    public $score   = NULL;
    public $contact = NULL;
    public $badges = array();
    public $attempts = array();
    public $badges_enabled = false;
    public $password_hash = NULL;

    // LDAP Attributes

    public $cn = NULL;
    public $mail = NULL;
    public $uconnPersonAffiliation = NULL;
    public $eduPersonAffiliation = NULL;
    public $title = NULL;

    function __construct($netid = NULL) {

        if (!is_null($netid)) 
            $this->load($netid);

    }
    public static function delete_user($netid) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'DELETE FROM users WHERE netid = :netid';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':netid', $netid);
        $stmt->execute();
      }
    }
    public static function email_is_taken($email) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT * FROM users WHERE contact=:contact';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':contact', $email);
        $stmt->execute();
        return ($stmt->fetchColumn() > 0);
      }
    }
    public static function alias_is_taken($alias) {
      if ($alias == '' || is_null($alias)) {
        return false;
      }
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT * FROM users WHERE alias=:alias';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':alias', $alias);
        $stmt->execute();
        return ($stmt->fetchColumn() > 0);
      }
    }
    public static function select_all() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT netid FROM users';
      $ret = array();
      if ($stmt = $db->prepare($SQL)) {
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $ret[] = new HuskyHuntUser($row['netid']);
        }
        return $ret;
      }
    }
    public static function select_all_by_has_badges($badges_enabled = true) {
      $users = self::select_all();
      $ret = array();
      foreach ($users as $user) {
        if ($user->badges_enabled == $badges_enabled) {
          $user->load_attempts();
          $ret[] = $user;
        }
      }
      return $ret;
    }
    public static function avg_retention_rate_by_has_badges($badges_enabled = true) {
      $users = self::select_all_by_has_badges($badges_enabled);
      $avg = 0;
      foreach ($users as $user) {
        $avg += $user->retention_rate();
      }
      return $avg/count($users);
    }
    public static function from_id($user_id) {
      
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT netid FROM users WHERE user_id = :user_id';
      
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindValue(':user_id', $user_id);
        if ($result = $stmt->execute()) {
          if ($stmt->rowCount() == 0) {
            return false;
          }
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $netid = $row['netid'];
          return new HuskyHuntUser($netid);
        }
      }
    }
    public static function calculate_score($score, $seconds_passed) {
      return ceil((float) $score / 3 + ((float) 2 / 3) * $score * pow((float) 1 / 4, (float) $seconds_passed / 86400));
    }
    public function scores() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT netid, alias, score FROM users WHERE netid != \'\' ORDER BY score DESC';
      $rank = 0;
      $previous_score = PHP_INT_MAX;
      $rows = array();
      if ($stmt = $db->prepare($SQL)) {
        $result = $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $name = $row['netid'];
          if (!is_null($row['alias']) && strlen($row['alias']) > 1) {
            $name = $row['alias'];
          }
          $score = $row['score'];
          if (!is_null($name) && !is_null($score)) {
            if ($score < $previous_score)
              $rank ++;
            $new_row = array('rank' => $rank, 'netid' => $name, 'points' => $score);
            if ($row['netid'] == $this->netid)
              $new_row['self'] = true;
            $rows[] = $new_row;
            $previous_score = $score;
          }
        }
      }
      echo json_encode($rows);
    }
    public function get_ldap_data() {
      if ($ds = ldap_connect(LDAPURL)) {
        @ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (@ldap_bind($ds, LDAPBINDDN, LDAPBINDPW)) {
          $entries = ldap_get_entries($ds, ldap_search($ds, LDAPBASEDN, "uid=" . $this->netid));
          if ($entries["count"] == 1) {
            $this->cn = $entries[0]['cn'][0];
            $this->mail = $entries[0]['mail'][0];
            $this->uconnPersonAffiliation = $entries[0]['uconnpersonaffiliation'];
            $this->eduPersonAffiliation = $entries[0]['edupersonaffiliation'];
            if (isset($entries[0]['title']))
              $this->title = $entries[0]['title'][0];
          }
        } else {
          return false;
        }
      } else {
        return false;
      }
    }
    public function count_active_days() {
      $days = new Set();
      foreach ($this->attempts as $attempt) {
        $days->push(floor($attempt->time/86400));
      }
      return $days->size();
    }
    public function has_completed_content_area($ca) {
      $db = HuskyHuntDatabase::shared_database();
      $complete = false;
      if (count($ca->module_ids) > 0)
        $complete = true;
      foreach ($ca->module_ids as $m_id) {
        $SQL = 'SELECT * FROM grades WHERE module_id = :module_id AND user_id = :user_id AND complete = 1';
        if ($stmt = $db->prepare($SQL)) {
          $stmt->bindParam(':module_id', $m_id);
          $stmt->bindParam(':user_id', $this->user_id);
          $stmt->execute();
          $complete &= $stmt->rowCount() > 0;
        }
      }
      return $complete;
    }
    public function retention_rate() {
      $days_passed = ceil(min(time(), HuskyHunt::get_statistic_end_date()) - $this->date_joined)/86400;
      return (float) $this->count_active_days()/$days_passed;
    }

    public function num_completed_levels() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT scavenger_hunt, other FROM completed WHERE user_id=:user_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindValue(':user_id', $this->user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $num_scavenger_hunt = $row['scavenger_hunt'];
        $num_other = $row['other'];
        return $num_scavenger_hunt + $num_other;
      }
    }
    public function num_completed_scavenger_levels() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT scavenger_hunt FROM completed WHERE user_id=:user_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindValue(':user_id', $this->user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['scavenger_hunt'];
      }
    }
    public function reset_player() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'DELETE FROM grades WHERE user_id = :user_id';
      $success = 1;
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindValue(':user_id', $this->user_id);
        $success &= $stmt->execute();
      }
      $SQL = 'DELETE FROM map_bu WHERE user_id = :user_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindValue(':user_id', $this->user_id);
        $success &= $stmt->execute();
      }
      $SQL = 'DELETE FROM users WHERE netid = :netid';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindValue(':netid', $this->netid);
        $success &= $stmt->execute();
      }
      return $success;
    }
    public function daily_module() {

        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT * FROM (SELECT module_id FROM (modules LEFT JOIN timeline USING (module_id)) WHERE start < NOW() AND stop > NOW() AND bonus = TRUE ORDER BY start, module_id) AS t WHERE NOT EXISTS (SELECT * FROM grades WHERE module_id = t.module_id AND user_id = :user_id AND complete = TRUE) LIMIT 1;';

        $module_id = NULL;

        if ($stmt = $db->prepare($SQL)) {
            
            $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);

            $result = $stmt->execute();

            
            if ($result) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $module_id = $row['module_id'];
            }
        }

        return $module_id;
    }
    function login($password) {
      if ($ds = ldap_connect(LDAPURL)) {
        @ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (@ldap_bind($ds, 'uid=' . $this->netid . ',ou=people,dc=uconn,dc=edu', $password)) {
          return JWT::encode(array('exp' => time() + 86000*3, 'netid' => $this->netid), HuskyHunt::$jwt_key);
        } else {
          return false;
        }
      } else {
        return false;
      }
    }
    public static function forgot_password($email) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT netid FROM users WHERE contact = :contact';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':contact', $email);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $token = HuskyHunt::encode(array('exp' => time() + 86000, 'netid' => $result['netid']));
        $headers = "From: account@" . DOMAIN_NAME . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (mail($email, 'HuskyHunt Password Change Request', 'To reset your password, <a href="' . BASE_URL . '/forgot.php?token=' . $token . '">click here</a>', $headers)) {
          return true;
        } else {
          return false;
        }
       
      }
    }
    function badges_json() {
      $badges = array();
      foreach ($this->badges as $badge) {
        $badges[] = array(
          'name' => $badge->name,
          'image' => $badge->image,
          'unearned_desc' => $badge->unearned_desc,
          'earned_desc' => $badge->earned_desc
        );
      }
      echo json_encode($badges);
    }

    function postponed_quiz_modules() {
        
        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT * FROM (SELECT module_id FROM (modules LEFT JOIN timeline USING (module_id)) WHERE (start < NOW() AND (stop > NOW() OR postponable = TRUE)) ORDER BY start ASC) AS postponable_modules WHERE NOT EXISTS (SELECT * FROM grades WHERE module_id = postponable_modules.module_id AND user_id = :user_id AND complete = TRUE)';
        
        $module_id = Array();

        if ($stmt = $db->prepare($SQL)) {
            
            $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);

            $result = $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $module_ids[] = $row['module_id'];
            }
        }

        return $module_ids;

    }

    function postponed_share_modules() {
        
        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT * FROM (SELECT module_id FROM (modules LEFT JOIN timeline USING (module_id)) WHERE (start < NOW() AND (stop > NOW() OR postponable = TRUE)) ORDER BY start ASC) AS postponable_modules WHERE NOT EXISTS (SELECT * FROM grades WHERE module_id = postponable_modules.module_id AND user_id = :user_id AND shared = TRUE) AND EXISTS (SELECT * FROM grades WHERE module_id = postponable_modules.module_id AND user_id = :user_id AND complete = TRUE)';
        $module_ids = Array();

        if ($stmt = $db->prepare($SQL)) {
            
            $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);

            $result = $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $module_ids[] = $row['module_id'];
            }
        }

        return $module_ids;
    }

    function share_eligible($module_id = NULL) {
    
        $db = HuskyHuntDatabase::shared_database();
        $SQL        = 'SELECT TRUE AS eligible FROM (SELECT TRUE) as temp WHERE EXISTS (SELECT * FROM grades WHERE module_id = :module_id AND user_id = :user_id AND complete = TRUE AND shared = FALSE) AND EXISTS (SELECT TRUE as module_active FROM (modules LEFT JOIN timeline USING (module_id)) WHERE ((module_id = :module_id AND start < NOW()) AND ((postponable = TRUE) OR (stop > NOW()))))';
        #$SQL = 'SELECT TRUE as eligible FROM (SELECT TRUE as complete_unshared FROM grades WHERE module_id = :module_id AND user_id = :user_id AND complete = TRUE AND shared = FALSE) AS completeness LEFT JOIN (SELECT TRUE as active FROM modules LEFT JOIN timeline USING (module_id) WHERE ((start < NOW() AND stop > NOW()) OR (start < NOW() and postponable = TRUE)) AND module_id = :module_id) AS activeness ON completeness.complete_unshared = activeness.active';
         $eligible = false;

        if ($stmt = $db->prepare($SQL)) {

            $stmt->bindValue(':module_id', $module_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);

            $db->beginTransaction();
            
            $stmt->execute();
            
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eligible = $result['eligible'];
            }

            $db->commit();
        }
        
        return $eligible;       
    }

    function quiz_eligible($module_id = NULL) {
        
        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT TRUE AS incomplete FROM (SELECT TRUE) as temp WHERE NOT EXISTS (SELECT * FROM grades WHERE module_id = :module_id AND user_id = :user_id AND complete = TRUE) AND EXISTS (SELECT TRUE as module_active FROM (modules LEFT JOIN timeline USING (module_id)) WHERE ((module_id = :module_id AND start < NOW()) AND ((postponable = TRUE) OR (stop > NOW()))))';
        #$SQL = 'SELECT TRUE as eligible FROM (SELECT TRUE as incomplete FROM grades WHERE NOT EXISTS (SELECT TRUE FROM grades WHERE module_id = :module_id AND user_id = :user_id AND complete = TRUE)) AS completeness LEFT JOIN (SELECT TRUE as active FROM modules LEFT JOIN timeline USING (module_id) WHERE ((start < NOW() AND stop > NOW()) OR (start < NOW() and postponable = TRUE)) AND module_id = :module_id) AS activeness ON completeness.incomplete = activeness.active';
        $eligible = false;

        if ($stmt = $db->prepare($SQL)) {

            $stmt->bindValue(':module_id', $module_id, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);

            $db->beginTransaction();
            
            $stmt->execute();
            
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eligible = $result['incomplete'];
            }

            $db->commit();
        }
        
        return $eligible;
    }

    function next_module() {

        $db         = HuskyHuntDatabase::shared_database();
        $SQL        = 'SELECT DISTINCT(module_id) FROM (SELECT module_id FROM modules LEFT JOIN timeline USING (module_id) WHERE bonus != TRUE AND start < NOW() AND stop > NOW() ORDER BY start) AS active_modules WHERE module_id NOT IN (SELECT module_id FROM grades WHERE user_id = :user_id AND complete = TRUE) LIMIT 1';
        #$SQL        = 'SELECT active_modules.module_id FROM ((SELECT * FROM timeline WHERE start < NOW() AND stop > NOW()) AS active_modules LEFT OUTER JOIN (SELECT * FROM grades WHERE user_id = :user_id) AS user_grades ON active_modules.module_id = user_grades.module_id) WHERE complete != TRUE LIMIT 1;';
        $result     = false; 
        $row        = NULL;
        $module_id  = NULL;

        if (!is_null($this->user_id) && ($stmt = $db->prepare($SQL))) {

            $stmt->bindValue(':user_id',  $this->user_id, PDO::PARAM_INT);
        
            $db->beginTransaction();

            $result = $stmt->execute();

            if (!$result && HH_DEBUG) { 
                
            } else {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $module_id = $row['module_id'];
            }

            $db->commit();
        }

        return (is_null($module_id) ? NULL : new HuskyHuntModule($module_id));
    }

    # user declares that they want to take the test
    function initialize_grade($module) {
        
        #$SQL    = 'INSERT INTO grades (user_id, module_id, complete, attempts) SELECT (:user_id, :module_id, FALSE, 0)';
        $SQL    = 'INSERT IGNORE INTO grades (user_id, module_id, complete, attempts) VALUES (:user_id, :module_id, FALSE, 0)';
        $db     = HuskyHuntDatabase::shared_database();
        $result = false;

        if ($module instanceof HuskyHuntModule) {

            if ($stmt = $db->prepare($SQL)) {
    
                $stmt->bindValue(':user_id',    $this->user_id,     PDO::PARAM_INT);
                $stmt->bindValue(':module_id',  $module->get_id(),  PDO::PARAM_INT);


                $db->beginTransaction();
                
                $result = $stmt->execute();

                if (!$result && HH_DEBUG) {
                   // TODO LOG ERROR 
                   die($stmt->errorCode());
                }  

                $db->commit();

            }
        }

        return $result;
    }

    function quiz_attempt($module, $results) {

        $SQL    = 'UPDATE grades SET attempts = attempts + 1, complete = :complete WHERE user_id = :user_id AND module_id = :module_id';
        $db     = HuskyHuntDatabase::shared_database();
        $result = false;
    
        $complete = true;

        foreach ($results as $question_id => $success) {
            $complete &= $success;
        }

        if ($module instanceof HuskyHuntModule) { 

            if ($stmt = $db->prepare($SQL)) { 

                $stmt->bindValue(':user_id',    $this->user_id,     PDO::PARAM_INT);
                $stmt->bindValue(':module_id',  $module->get_id(),  PDO::PARAM_INT);
                $stmt->bindValue(':complete',   $complete,          PDO::PARAM_BOOL);

                $db->beginTransaction();

                $result = $stmt->execute();

                if (!$result && HH_DEBUG) { 
                    die($stmt->errorCode());
                }
            
                $db->commit();
                

            }
        }
   
        if ($result && $complete) {
            $this->score += $module->current_points();
            $this->save();
        }

        return $result;
        
    }

    function share_success($module_id, $service_id, $data) {

        $GRADES_SQL = 'UPDATE grades SET shared = TRUE WHERE user_id = :user_id AND module_id = :module_id';
        $SOCIAL_SQL = 'INSERT INTO social (user_id, module_id, service_id, datetime, data) VALUES (:user_id, :module_id, :service_id, NOW(), :data)';
        $db     = HuskyHuntDatabase::shared_database();
        $result = true;
  
        $module_id  = (is_numeric($module_id) ? intval($module_id) : NULL);
        $service_id = (is_numeric($service_id) ? intval($service_id) : NULL);
        if (!is_string($data))
            $data = serialize($data);

        if ($stmt = $db->prepare($GRADES_SQL)) { 

            $stmt->bindValue(':user_id',    $this->user_id, PDO::PARAM_INT);
            $stmt->bindValue(':module_id',  $module_id,     PDO::PARAM_INT);

            $db->beginTransaction();

            $result &= $stmt->execute();

            if (!$result && HH_DEBUG) { 
                die($stmt->errorCode());
            }
        
            $db->commit();

        }
       
        if ($stmt = $db->prepare($SOCIAL_SQL)) { 

            $stmt->bindValue(':user_id',    $this->user_id, PDO::PARAM_INT);
            $stmt->bindValue(':module_id',  $module_id,     PDO::PARAM_INT);
            $stmt->bindValue(':service_id', $service_id,    PDO::PARAM_INT);
            $stmt->bindValue(':data',       $data,          PDO::PARAM_STR);

            $db->beginTransaction();

            $result &= $stmt->execute();

            if (!$result && HH_DEBUG) { 
                die($stmt->errorCode());
            }
        
            $db->commit();
        }

        if ($result) {
            $module = new HuskyHuntModule($module_id);
            $this->score += $module->social_points;
            $this->save();
        }
    
        return $result;
    }

    function load($netid) {
    
        $SQL = 'SELECT user_id, date_joined, netid, alias, password, role, badges_enabled, score, contact FROM users WHERE netid=:netid';
        $db = HuskyHuntDatabase::shared_database();

        if (!is_null($netid)) {
            
            if ($stmt = $db->prepare($SQL)) {
         
                $stmt->bindParam(':netid', $netid);

                $stmt->execute();

                if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  if ($stmt->rowCount() == 0) {
                    return false;
                  } 
                    $this->user_id      = $result['user_id'];
                  $this->netid    = $result['netid'];
                    $this->alias = $result['alias'];
                    $this->role    = $result['role'];
                    $this->score    = $result['score'];
                    $this->contact  = $result['contact'];
                    $this->password_hash = $result['password'];
                    $this->date_joined = strtotime($result['date_joined']);
                    if ($result['badges_enabled']) {
                      $this->badges_enabled = true;
                    } else {
                      $this->badges_enabled = false;
                    }

                } else {
                    $this->netid = $netid;
                    $this->save();
                }

            } else {
                #TODO handle sql erros
            }
          $this->load_badges();
        }
    }
    function get_rank() {
        $db = HuskyHuntDatabase::shared_database();
        $SQL = 'SELECT netid, real_points, score FROM (SELECT user_id, SUM(((points * complete) + (social_points * shared))) as real_points  FROM modules LEFT JOIN grades USING (module_id) GROUP BY user_id ORDER BY points DESC) AS calculated LEFT JOIN users USING (user_id) ORDER BY real_points DESC';
        $rank = 0;
        $previous_score = PHP_INT_MAX;
        $rows = array();
        if ($stmt = $db->prepare($SQL)) {

            $result = $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                $netid = $row['netid'];
                $score = $row['real_points'];
                if (!is_null($netid) && !is_null($score)) {
                  if ($score < $previous_score)
                    $rank ++;
                  if ($row['netid'] == $this->netid)
                    return $rank;
                  $previous_score = $score;
                }
            }
        }
    }
    function get_rank_remix() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT netid, alias, score FROM users WHERE netid != \'\' ORDER BY score DESC';
      $rank = 0;
      $previous_score = PHP_INT_MAX;
      $rows = array();
      if ($stmt = $db->prepare($SQL)) {
        $result = $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $name = $row['netid'];
          if (!is_null($row['alias']) && strlen($row['alias']) > 1) {
            $name = $row['alias'];
          }
          $score = $row['score'];
          if (!is_null($name) && !is_null($score)) {
            if ($score < $previous_score)
              $rank ++;
            if ($row['netid'] == $this->netid)
              return $rank;
            $previous_score = $score;
          }
        }
      }
    }
      
    function scavenger_modules_completed() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT scavenger_hunt FROM completed WHERE user_id = :user_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          return $result['scavenger_hunt'];
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    function other_modules_completed() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT other FROM completed WHERE user_id = :user_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          return $result['other'];
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    function insert_badge_by_id($badge_id) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT * FROM map_bu WHERE user_id = :user_id;';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          if ($result['badge_id'] == $badge_id) {
            return false;
          }
        }
      }

      $SQL = 'INSERT INTO map_bu (user_id, badge_id, time_awarded) VALUES (:user_id, :badge_id, NOW());';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':badge_id', $badge_id);
        $stmt->execute();
        return true;
      } else {
        return false;
      }
    }
    function load_attempts() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT attempt_id FROM attempts WHERE user_id = :user_id';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        $attempts = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $attempts[] = new HuskyHuntAttempt($row['attempt_id']);
        }
        $this->attempts = $attempts;
      }
    }

    function load_badges() {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'SELECT * FROM map_bu WHERE user_id = :user_id;';
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $this->badges[] = new HuskyHuntBadge($result['badge_id']);
        }
      }
    }
    function set_password_without_commit($pass) {
      $this->password_hash = hash("sha256", $pass);
    }
    function set_password($pass) {
      $db = HuskyHuntDatabase::shared_database();
      $SQL = 'UPDATE users SET password=:password WHERE netid=:netid';
      $password_hash = hash("sha256", $pass);
      $this->password_hash = $password_hash;
      if ($stmt = $db->prepare($SQL)) {
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':netid', $this->netid);
        $result = $stmt->execute();
        if ($result) {
          return true;
        }
      }
      return false;
    }
    function save() {
        
        $db         = HuskyHuntDatabase::shared_database();
        $INSERT_SQL = 'INSERT INTO users (netid, alias, contact, badges_enabled, password, date_joined) VALUES (:netid, :alias, :contact, :badges_enabled, :password, NOW())';
        $UPDATE_SQL = 'UPDATE users SET password=:password, alias=:alias, score=:score, badges_enabled=:badges_enabled, contact=:contact WHERE netid=:netid';
        $SQL        = (is_null($this->user_id)) ? $INSERT_SQL : $UPDATE_SQL;

        $result     = false;

        if (!is_null($this->netid)) {
            
            if ($stmt = $db->prepare($SQL)) {
                
              $stmt->bindParam(':netid',      $this->netid);
              $stmt->bindParam(':password',   $this->password_hash);
              $stmt->bindParam(':alias', $this->alias);
              $stmt->bindParam(':contact', $this->contact);
              if ($this->badges_enabled) {
                $badges_enabled = 1;
              } else {
                $badges_enabled = 0;
              }
                $stmt->bindParam(':badges_enabled', $badges_enabled);
                if ($SQL == $UPDATE_SQL) { 
                    $stmt->bindParam(':score',      $this->score);
                }
    
                $db->beginTransaction();
                $stmt->execute(); 
               
                if ($SQL == $INSERT_SQL) { 
                    $result = $db->lastInsertId();
                    if ($result != 0) 
                        $this->user_id = $result;
                } else {
                    $result = ($stmt->rowCount() === 1);
                }

                $db->commit();
            } 
        }

        return $result;
    }


}

if (!isset($_SESSION)) { session_start(); }

$USER = (isset($NETID)) ? new HuskyHuntUser($NETID) : NULL; 



