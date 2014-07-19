<?php require_once './hh-config.php'; ?>
<?php 

    $huskyhunt = new HuskyHunt();

    $USER->save();

    if (!is_null($USER)) {
        $module = $USER->next_module();

        if (!is_null($module)) {
            $_SESSION['MODULE_ID'] = $module->get_id();    
        } else {
            unset($_SESSION['MODULE_ID']);
        }
    } else {
        
    }

    $daily_module_id = $USER->daily_module();
    
?>
<html>
  <head>
    <?php include BASE_PATH . '/templates/head.php'; ?>
  </head>
  <body ng-app="huskyhunt" animation="slide-left-right-ios7">
    <ion-nav-bar type="bar-positive" back-button-type="button-icon" back-button-icon="ion-arrow-left-c"  class="nav-title-slide-ios7">
    </ion-nav-bar>
    <ion-nav-view></ion-nav-view>
  </body>
</html>
