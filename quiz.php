<?php require_once './hh-config.php'; ?>
<?php 

    

    $huskyhunt = new HuskyHunt();
    if (!$huskyhunt->has_begun() || $huskyhunt->has_ended()) {
        header('location: index.php');
        die();
    }

    $module_id = request_value('MODULE_ID');
    if (is_null($module_id))
        $module_id = session_value('MODULE_ID');

    if (is_null($module_id) || !$USER->quiz_eligible($module_id)) {
        if (!is_null($module_id) && $USER->share_eligible($module_id))
            redirect('share.php');
        header('Location: index.php');
        die();
    }

    if (!is_null($USER)) {
        
        $module = new HuskyHuntModule($module_id);
        $USER->initialize_grade($module);
        $_SESSION['MODULE_ID'] = $module_id;
    }

?>
<html>

    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
        <script type="text/javascript" src="<?=BASE_URL?>/js/jquery-ui.js"></script>
        <script type="text/javascript">
      
        $(document).ready(function() {
            
            function evaluate_grade(data) {
                
                var CORRECT = '<?=BASE_URL?>/images/tick.png';            
                var INCORRECT = '<?=BASE_URL?>/images/cross.png';            

                var overall = true;

                for (qid in data) {
                    
                    dom_id = "#GRADE_IMG" + qid;
                    element = $(dom_id);
                    element.attr("src", (data[qid] ? CORRECT : INCORRECT));
             
                    element.css("display", "inline");
                    overall &= data[qid];

                }       
            
                if (overall) {
                    //$('#grade-quiz').switchClass('pull-left', 'pull-right');
                    $('#grade-quiz').html('Continue!');                
                    var distance = $('#grade-quiz').closest('div').width() - $('#grade-quiz').outerWidth() + 15;
                    $('#grade-quiz').animate({left: distance}, 1000);

                    $('#grade-quiz').unbind('click');
                    $('#grade-quiz').click(function() {
                        window.location = 'share.php';
                    });
                }
            }
     
            function grade() {    
                
                var URL = '<?=BASE_URL?>/ajax/grade.php';
           
                $.ajax({
                    type:   'POST',
                    dataType: "json",
                    url:    URL,
                    data:   $('#quiz').serialize(),
                    success: evaluate_grade

                }); 
                
                return false;
            }
            
            $('#grade-quiz').click(grade);

        });

        </script>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div class="container" style="background: white; min-height: 200px; margin-bottom: 30px;">
           
            <?php if (!is_null($module)) { ?> 
                
                <form role="form" id="quiz" action="">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="well" style="background-color: transparent">
                                <?php 
                                $index = 0;
                                foreach ($module->questions as $question) { 
                                    $index++;
                                ?>
                                <img class="pull-left hh-grade" id="GRADE_IMG<?=$question->get_id()?>" />
                                <div class="row">
                                    <div class="col-md-11" style="vertical-align: top;">
                                        <h4 style="margin-top: 0; padding-right: 10px;" class="pull-left"><?=$index?>.</h4>
                                <!--<h3 style="height: 24px">-->
                                    <?=$question->body?> 
                                <!--</h3>-->
                                </div>
                                </div>
                                <?php $question->render_answers(); ?>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <button id="grade-quiz" type="button" class="btn btn-lg btn-primary">Grade It!</button>
                        </div>
                    </div>
                </form>

                <?php } ?>

        </div>
    </body>
</html>

