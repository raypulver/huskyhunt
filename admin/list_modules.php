<?php require_once '../hh-config.php'; ?>
<?php require_once './hh-admin.php'; ?>
<?php $ADMIN->page_validate(); ?>
<html ng-app="huskyadmin">

    <head>
        <?php include BASE_PATH . '/admin/head.php'; ?>
        <script type="text/javascript" src="<?=BASE_URL?>/admin/js/modules.js"></script>
        <script type="text/javascript" src="js/contentareas.js"></script>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>
        <div class="container" ng-controller="contentAreasCtrl">
          
            <div class="row">
              <h1>Content Areas</h1>
            </div>
            <div class="row">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th></th>
                    <th class="icon-cell"><a ng-click="newContentArea()"><img class="img24x24" src="/images/plus_button.png" alt="add"></a></th>
                  </tr>
                </thead>
                <tbody>
                  <tr ng-repeat="contentArea in contentAreas" class="netid-animation">
                    <td>{{contentArea.name}}</td>
                    <td class="icon-cell"><a ng-click="editContentArea(contentArea)"><img style="cursor: pointer" class="img24x24" src="/images/edit_button.png" alt="edit" /></a></td>
                    <td class="icon-cell"><a ng-click="removeContentArea(contentArea)"><img style="cursor: pointer" class="img24x24" src="/images/prohibition_button.png" alt="delete" /></a></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="row">
                <h1>Modules</h1>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <!--<th>ID</th>-->
                            <th>ID</th>
                            <th>Title</th>
                            <th></th>
                            <th class="icon-cell"><a href="modules.php?module_id=new"><img class="img24x24" src="<?=BASE_URL?>/images/plus_button.png" alt="add" /></a></th>
                        </tr>
                    </thead>
                    <tbody>
                    
                        <?php 
                            $modules = $ADMIN->get_modules();
                            if (!empty($modules)) {
                                foreach($modules as $module) { 
                        ?>
                            <tr>
                                <td style="width: 30px"><?=$module['module_id']?></td>
                                <td><?=$module['title']?></td>
                                <td class="icon-cell">
                                <td class="icon-cell"><a href="modules.php?module_id=<?=$module['module_id']?>"><img class="img24x24" src="<?=BASE_URL?>/images/edit_button.png" alt="edit" /></a></td>
                                <td class="icon-cell"><img class="img24x24" src="<?=BASE_URL?>/images/prohibition_button.png" alt="delete" /></td>
                            </tr>
                        <? 
                            } 
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </body>
</html>

