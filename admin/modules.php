<?php require_once '../hh-config.php'; ?>
<?php require_once './hh-admin.php'; ?>
<?php $ADMIN->page_validate(); ?>
<?php 
    
    $module_id  = request_value('module_id');

    if (!is_numeric($module_id) && (strtolower($module_id) != 'new')) {
        header('Location: list_modules.php');
        die();
    }

    $module     = new HuskyHuntModule($module_id); 

    if (is_null($module->get_id())) {
        $module->save();
        # this will ensure that our module has a valid ID
    }

    #$games      = $admin->get_games();

?>
<html>
    <head>
        <?php include BASE_PATH . '/admin/head.php'; ?>
        <script type="text/javascript" src="<?=BASE_URL?>/admin/js/modules.js"></script>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>

        <div class="container" style="margin-bottom: 15px;">
            <form role="form" id="module-form" method="POST">
                <input type="hidden" name="module_id" value="<?=$module->get_id()?>">
                <div class="row">
                    <div class="col-md-6">
                <h2 style="margin-top: 5px;"> HuskyHunt Module Editor: </h2>
                    </div>
                    <div class="col-md-6">
                        <button class="pull-right btn btn-lg btn-primary" onclick="window.open('<?=BASE_URL?>/admin/preview.php?module_id=<?=$module_id?>', '_blank');">Preview</button>
                        <button id="save_module" type="button" class="pull-right btn btn-lg btn-primary" style="margin-right: 10px;" onclick="HuskyHuntModuleUI.save(this);">Save Changes</button>
                        </div>

                </div>
                <div class="row">
                    <div class="col-md-12">
                        <hr />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="module_title"><h4>Title:</h4></label>
                            <input class="form-control" type="text" name="module_title" value="<?=htmlspecialchars($module->title)?>" />
                        </div>
                    </div>
                </div>
                <div class="row">
                  <div class="col-md-9">
                    <div class="form-group">
                      <label for="vendor"><h4>Vendor</h4>
                      <input class="form-control" type="text" name="module_vendor" value="<?=$module->vendor?>">
                    </div>
                  </div> 
                </div>
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group">
                            <label for="module_insight"><h4>Social Text:</h4></label>
                            <input class="form-control" type="text" name="module_insight" value="<?=htmlspecialchars($module->insight)?>" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="insight_character_count"><h4>Character Counter:</h4></label>
                            <input class="form-control" type="text" name="insight_character_count" value="<?=strlen($module->insight)?>" readonly />
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="module_insight"><h4>Module Points:</h4></label>
                            <input class="form-control" type="text" name="module_points" value="<?=htmlspecialchars($module->points)?>" />
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="module_insight"><h4>Social Points:</h4></label>
                            <input class="form-control" type="text" name="module_social_points" value="<?=htmlspecialchars($module->social_points)?>" />
                        </div>
                    </div>
                    <div class="col-md-2" style="padding-top: 25px;">
                        <div class="checkbox">
                            <label for="module_decay">
                                <input type="checkbox" name="module_decay" <?=($module->decay ? 'checked' : '')?>> <h4>Point Decay</h4>
                            </label>
                        </div>
                        <div class="checkbox">
                            <label for="module_postponable">
                                <input type="checkbox" name="module_postponable" <?=($module->postponable ? 'checked' : '')?> /> <h4>Postponable</h4>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label for="module_bonus">
                                <input type="checkbox" name="module_bonus" <?=($module->bonus ? 'checked' : '')?> /> <h4>Daily Module</h4>
                            </label>
                        </div>

                        <div class="checkbox">
                            <label for="module_knowledge_base">
                                <input type="checkbox" name="module_knowledge_base" <?=($module->knowledge_base ? 'checked' : '')?> /> <h4>Knowledge Base</h4>
                            </label>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="module_body"><h4>Content:</h4></label>
                            <textarea name="module_body" class="ckeditor"><?=$module->body?></textarea>
                        </div>
                    </div>
                </div>
               
                <h4> Timeline: </h4>
                
                <div class="well" id="timeline">
                    <div class="row">
                        <div class="col-md-6 col-md-offset-3" style="margin-top: 10px;">
                            <table class="table">
                           
                                <thead>
                                    <tr>
                                        <th>
                                            <label for="">Start Date:</label>
                                            <div class="input-group date" id="datetimepicker-start">
                                                <input type="text" data-format="yyyy-MM-dd hh:mm:ss" class="form-control" value="<?=date('Y-m-d H:i:s')?>" />
                                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                            </div>
                                        </th>
                                        <th>
                                            <label for="">End Date:</label>
                                            <div class="input-group date" id="datetimepicker-stop">
                                                <input type="text" data-format="yyyy-MM-dd hh:mm:ss" class="form-control" value="<?=date('Y-m-d H:i:s')?>"/>
                                                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                            </div>
                                        </th>
                                        <th class="icon-cell">
                                            <a href="#add_timeline" onclick="HuskyHuntModuleUI.add_timeline(this);">
                                                <img class="img24x24" src="<?=BASE_URL?>/images/plus_button.png" alt="add" style="margin-top: 27px;" />
                                            </a>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach($module->get_timeline() as $timeline) { ?>
                                    <tr timeline_id=<?=$timeline['timeline_id']?>>
                                        <td><?=$timeline['start']?></td>
                                        <td><?=$timeline['stop']?></td>
                                        <td class="icon-cell">
                                            <a href="#remove_timeline" onclick="HuskyHuntModuleUI.remove_timeline(this);">
                                                <img class="img24x24" src="<?=BASE_URL?>/images/prohibition_button.png" alt="edit" />
                                            </a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row"> 
                    <div class="col-md-12">

                        <h4>Questions:</h4>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Question:</th>
                                    <th class="icon-cell"></th>
                                    <th class="icon-cell">
                                        <a onclick="HuskyHuntModule.new_question(this)" question_id="NEW" href="#new_question" id="new-question">
                                            <img class="img24x24" src="<?=BASE_URL?>/images/plus_button.png" alt="add" />
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="module-questions">     

                            <?php foreach($module->questions as $question) { ?>

                                <tr question_id="<?=$question->get_id()?>">
                                    <td class="question-body"><?=$question->body?></td>
                                    <td class="icon-cell">
                                        <a href="#edit_question" onclick="HuskyHuntQuestionModal.show(this)">
                                            <img class="img24x24" src="<?=BASE_URL?>/images/edit_button.png" alt="edit" />
                                        </a>
                                    </td>
                                    <td class="icon-cell">
                                        <a href="#delete_question" onclick="HuskyHuntModule.delete_question(this)">
                                            <img class="img24x24" src="<?=BASE_URL?>/images/prohibition_button.png" alt="delete" />
                                        </a>
                                    </td>
                                </tr>

                            <?php } ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal fade" id="question-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Edit Question</h3>
                    </div>
                    <div class="modal-body">
                        <form role="form" id="question-form">
                            <input type="hidden" id="ajax-question-id" name="question_id" value="NULL" />
                            <div class="form-group"> 
                                <label for="question_body">Question:</label>
                                <textarea class="ckeditor" name="question_body" id="ajax-question-body"></textarea>
                            </div>

                            <hr />
                            <div class="form-group">
                                <label for="feedback_body">Feedback: (shown on wrong answer)</label><br>
                                <input type="text" style="width: 100%" name="feedback_body" id="ajax-feedback-body">
                            </div>
                            <div class="form-group">
                                <label for="ad_text">Ad Text:</label><br>
                                <input type="text" style="width: 100%" name="ad_text" id="ajax-ad-text">
                            </div>
                            <hr>
                            <h4 class="modal-title">Answers:</h4>
                            <div class="row"> 
                                <div class="col-md-12">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Answer</th>
                                        <th style="width: 50px;">Correct</th>
                                        <th style="width: 30px;">QR</th>
                                        <th class="icon-cell"></th>
                                        <th class="icon-cell">
                                            <a href="#new_answer" onclick="HuskyHuntQuestion.new_answer(this)">
                                                <img class="img24x24" src="<?=BASE_URL?>/images/plus_button.png" alt="add" />
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="ajax-question-answers">
                                    <!-- ajax call to populate the table -->
                                </tbody>
                            </table>
                                </div>
                            </div>
                              
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" onclick="HuskyHuntQuestionModal.hide(this);">Close</button>
                        <button type="button" class="btn btn-primary" onclick="HuskyHuntQuestionModal.save(this);">Save</button>
                        <button type="button" class="btn btn-primary" onclick="HuskyHuntQuestionModal.save_hide(this);">Save &amp Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="answer-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Edit Answer</h3>
                    </div>
                    <div class="modal-body">
                        <form role="form">
                            <input type="hidden" name="answer_id" value="NULL" />
                            <div class="form-group"> 
                                <label for="answer_body">Answer:</label>
                                <!--<textarea id='answer-ckeditor' class="ckeditor" name="answer_body"></textarea>-->
                                <textarea style="width: 100%" id="answer-body" type="text" name="answer_body"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" onclick="HuskyHuntAnswerModal.hide(this);">Close</button>
                        <button type="button" class="btn btn-primary" onclick="HuskyHuntAnswerModal.save(this);">Save</button>
                        <button type="button" class="btn btn-primary" onclick="HuskyHuntAnswerModal.save_hide(this);">Save &amp Close</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include BASE_PATH . '/templates/popup-modals.php'; ?>
        
    </body>
</html>

