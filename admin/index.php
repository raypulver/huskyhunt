<?php require_once '../hh-config.php'; ?>
<?php require_once './hh-admin.php'; ?>
<?php
  $ADMIN->module_redirect_if_already_logged_in();
?>
<html ng-app="huskyadmin">

    <head>
        <?php include BASE_PATH . '/admin/head.php'; ?>
      <script type="text/javascript" src="js/login.js"></script>
    </head>
    <body ng-controller="loginCtrl">
        <?php include BASE_PATH . '/templates/navigation.php'; ?>

        <div class="container">
          <div class="panel panel-default" style="margin-left: auto; margin-right: auto; max-width: 600px">
            <div class="panel-heading">
              <h3 class="panel-title"><strong>Sign in</strong></h3>
            </div>
            <div class="panel-body">
              <form role="form">
                <div class="form-group">
                  <label for="netid">NetID</label>
                  <input type="text" class="form-control" style="border-radius:0px" id="netid" ng-model="user.netid" placeholder="Enter NetID">
                </div>
                <div class="form-group">
                  <label for="password">Password <a ng-click="openModal()">(forgot password)</a></label>
                  <input type="password" class="form-control" style="border-radius:0px" id="password" ng-model="user.pass" placeholder="Password">
                </div>
                <button ng-click="login()" type="submit" class="btn btn-sm btn-default">Sign in</button>
              </form>
            </div>
          </div>
          <div id="alert-container" style="max-width: 600px; margin-left: auto; margin-right: auto"></div>
        </div>
<!--            <button onclick="window.location='emails.php'" class="btn btn-lg btn-primary" type="button">Generate Player Email List</button> -->
    </body>
</html>

