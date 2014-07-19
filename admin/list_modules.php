<?php require_once '../hh-config.php'; ?>
<?php require_once './hh-admin.php'; ?>
<?php $ADMIN->page_validate(); ?>
<html>

    <head>
        <?php include BASE_PATH . '/admin/head.php'; ?>
        <script type="text/javascript" src="<?=BASE_URL?>/admin/js/modules.js"></script>
    </head>
    <body>
        <?php include BASE_PATH . '/templates/navigation.php'; ?>

        <div class="container">
            <div class="row">
                <h1>Modules</h1>
                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <!--<th>ID</th>-->
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
                                <td><?=$module['title']?></td>
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

