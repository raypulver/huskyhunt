<?php require_once '../hh-config.php'; ?>
<?php require_once './hh-admin.php'; ?>
<?php $ADMIN->page_validate(); ?>
<?php 
    
    $module_id = request_value('module_id');

    $module = new HuskyHuntModule($module_id);

?>
<html>

    <head>
        <?php include BASE_PATH . '/admin/head.php'; ?>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div class="container" style="background: white; min-height: 200px;">

            <ul class="nav nav-tabs" id="myTab">
                <li class="active"><a href="#module" data-toggle="tab">Module</a></li>
                <li><a href="#quiz" data-toggle="tab">Quiz</a></li>
                <li><a href="#social" data-toggle="tab">Social</a></li>
            </ul>

            <div class="tab-content">
            <div class="tab-pane active" id="module">
            
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="well">
                            <h2> <?=$module->title?> </h2>
                            <hr /> 
                            <div><?=$module->body?> </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-md-offset-2">
                        <button id="mobile_provider" type="button" class="pull-right btn-lg btn-primary" onclick="">Take The Quiz!</button>
                    </div>
                </div>

            </div>
            <div class="tab-pane" id="quiz">
            
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
                                    <div class="col-md-1" style="vertical-align: top;">
                                        <h4 style="margin-top: 0;"><?=$index?>.</h4>
                                    </div>
                                <div class="col-md-10" style="">
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
                            <button id="grade_quiz" type="button" class="pull-right btn-lg btn-primary">Grade It!</button>
                        </div>
                    </div>
                </form>  

            </div>
            <div class="tab-pane" id="social">social</div>
            </div>

                   </div>
    </body>
</html>

