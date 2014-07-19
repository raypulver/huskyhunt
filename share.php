<?php require_once './hh-config.php'; ?>
<?php 
    #if (!is_null($USER)) {
    #    $module = $USER->next_module();
    #}
    
    $module_id = request_value('MODULE_ID');
    if (is_null($module_id))
        $module_id = session_value('MODULE_ID');

    if (is_null($module_id) || !$USER->share_eligible($module_id)) {
        header('Location: quiz.php');
        die();
    }

    if (!is_null($USER)) {
        
        $USER->initialize_grade($module);

        $module = new HuskyHuntModule($module_id);
        $_SESSION['MODULE_ID'] = $module_id;

    
    }

?>
<html>

    <head>
        <?php include BASE_PATH . '/templates/head.php'; ?>
        <script type="text/javascript" src="js/confetti.min.js"></script>
        <script type="text/javascript" src="js/share.js"></script>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        
        <div id="background" class="noselect"></div> 
        <div class="container" style="background: transparent; min-height: 200px;">
            <div class="row">
            

                <div class="col-md-8 col-md-offset-2">
                    <div class="well">

                        <h2> Share! </h2>
                        <h4> Share what you have learned on the social network of your choice.</h4>
                        <h4> If you just scanned a QR code you have already earned the points for this module! </h4> 
                        <hr />

                        <div class="row" style="text-align: center;">

                            <blockquote>
                                <?=$module->insight?>
                            </blockquote>

                            <div class="col-md-5 col-md-offset-1 ">
                                <a href="#twitter" onclick="HuskyHuntShareModal.show('TWITTER')">
                                    <img src="<?=BASE_URL?>/images/social_twitter_square.png" alt="Share on Twitter" title="Share on Twitter" />
                                </a>
                            </div>
                            <div class="col-md-5">
                                <a href="#facebook" onclick="HuskyHuntShareModal.show('FACEBOOK')">
                                    <img src="<?=BASE_URL?>/images/social_facebook_square.png" alt="Share on Facebook" title="Share on Facebook" />
                                </a>
                            </div>
                            <!--<div class="col-md-4">
                                <a href="https://plus.google.com/share?url=http://huskyhunt.uconn.edu/" onclick="javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">
                                    <img src="<?=BASE_URL?>/images/social_tumblr_square.png" alt="Share on Google Plus" title="Share on Google Plus" />
                                </a>
                            </div>-->
                        </div>

                        <br />
                        <h6> * Sharing is <b>not</b> required to be eligible for the grand prize.</h6>

                    </div>

                    <button id="mobile_provider" type="button" class="pull-right btn btn-lg btn-default" onclick="window.location='skip.php';">Skip</button>

                </div>
            </div>
        </div>

        <div class="modal fade" id="social-modal" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Share On Facebook!</h3>
                    </div>
                    <div class="modal-body">
                        <h4><?=$module->insight?></h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="HuskyHuntAnswerModal.save_hide(this);" id="share-button">Post to Facebook</button>
                    </div>
                </div>
            </div>
        </div>

    </body>
</html>

