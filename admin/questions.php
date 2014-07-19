<?php require_once '../hh-config.php'; ?>
<?php require_once './hh-admin.php'; ?>
<?php $ADMIN->page_validate(); ?>
<?php 
    
    $admin = new HuskyHuntAdmin();

    if (isset($_GET['qid'])) 
        $qid = $_GET['qid'];

?>
<html>

    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
        <?php include BASE_PATH . '/admin/head.php'; ?>
        <script type="text/javascript"> 

        $(document).ready(function() { 
            
            function save_question() {

                var URL = '<?=BASE_URL?>/admin/ajax/save_question.php';

                $.ajax({
                    type: 'POST',
                    url: URL,
                    dataType: "json",
                    data: $('#question_form').serialize(),
                    success: function(json) {

                        if (json == true) {
                            alert("Saved!");
                        }

                    }
                });

            }
        

            $('#save_question').click(save_question);
    
        });

        </script>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>

        <div class="container">
            
            <?php if (!isset($qid)) { ?>
            <div class="row">
                <h1>Questions</h1>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Question</th>
                            <th></th>
                            <th class="icon-cell"><a href="?qid=new"><img class="img24x24" src="<?=BASE_URL?>/images/add.png" alt="add" /></a></th>
                        </tr>
                    </thead>
                    <tbody>
                    
                        <?php 
                            $questions = $admin->get_questions();

                            if (!empty($questions)) {
                                foreach($questions as $question) { 
                        ?>
                            <tr>
                                <td style="width:50px;"><?=$question['qid']?></td>
                                <td><?=$question['body']?></td>
                                <td class="icon-cell"><a href="questions.php?qid=<?=$question['qid']?>"><img class="img24x24" src="<?=BASE_URL?>/images/edit_button.png" alt="edit" /></a></td>
                                <td class="icon-cell"><img class="img24x24" src="<?=BASE_URL?>/images/prohibition_button.png" alt="delete" /></td>
                            </tr>
                        <? 
                            } 
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
            <?php 
            
            } else { 
                $question = (is_numeric($qid)) ? new HuskyHuntQuestion($qid) : new HuskyHuntQuestion();
            ?>  
          
                <form role="form" id="question_form" method="POST">
                    <input type="hidden" name="qid" value="<?=$qid?>">
                    <div class="form-group">
                        <label for="question_body">Question Content:</label>
                        <textarea name="question_body" class="ckeditor"><?=$question->body?></textarea>
                    </div>
                </form>

                <button id="save_question" type="button" class="pull-right btn-primary">Save Changes</button>

            <?php 
            } 
            ?>




        </div>
    </body>
</html>

