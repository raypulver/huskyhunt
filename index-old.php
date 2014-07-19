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
<html ng-app="huskyhunt">

  <head>
    <?php include BASE_PATH . '/templates/head.php'; ?>
  </head>
  <body ng-controller="mainCtrl" animation="slide-left-right-ios7">
    <ion-nav-bar class="bar-positive nav-title-slide-ios7">
      <ion-nav-back-button class="button-icon icon ion-ios7-arrow-back">
        Back
      </ion-nav-back-button>
    </ion-nav-bar>
    <ion-nav-view></ion-nav-view>
  </body>
</html>
