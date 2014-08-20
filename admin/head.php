
<!-- begin admin head -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/datetimepicker.min.css" rel="stylesheet">
<link href="css/huskyhunt.css" rel="stylesheet">
<link href="../lib/angular-motion/dist/angular-motion.css" rel="stylesheet">

<script src="js/sprintf.js"></script>
<script src="js/jquery.js"></script>
<script src="js/jquery-ui.js"></script>
<script src="../lib/ng-file-upload/angular-file-upload-shim.js"></script>
<script src="../lib/angular/angular.js"></script>
<script src="../lib/ng-file-upload/angular-file-upload.js"></script>
<script src="../lib/angular-ui-router/release/angular-ui-router.js"></script>
<script src="../lib/angular-strap/dist/angular-strap.js"></script>
<script src="../lib/angular-strap/dist/angular-strap.tpl.js"></script>
<script src="../lib/angular-animate/angular-animate.js"></script>
<script src="ckeditor-full/ckeditor.js"></script>
<script src="ckeditor-full/adapters/jquery.js"></script>
<script src="js/filter.strip.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/datetimepicker.js"></script>
<script type="text/javascript">
angular.module('huskyadmin', ['ngAnimate', 'huskyhunt.filter.strip', 'ui.router', 'mgcrea.ngStrap', 'angularFileUpload']).controller('navCtrl', function ($scope) {
  $scope.loggedIn = <?= isset($_SESSION['netid']) ? 'true' : 'false' ?>;
});

$(document).ready(function() {
    CKEDITOR.config.customConfig = "<?=BASE_URL?>/admin/js/ckeditor.conf.js";
    var el = $('textarea.ckeditor')
    if (el) {
      el.ckeditor();
    }
});

</script>

<!-- end admin head -->
