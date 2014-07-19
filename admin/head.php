
<!-- begin admin head -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/datetimepicker.min.css" rel="stylesheet">
<link href="css/huskyhunt.css" rel="stylesheet">

<script src="js/sprintf.js"></script>
<script src="js/jquery.js"></script>
<script src="js/jquery-ui.js"></script>
<script src="ckeditor-full/ckeditor.js"></script>
<script src="ckeditor-full/adapters/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/datetimepicker.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    CKEDITOR.config.customConfig = "<?=BASE_URL?>/admin/js/ckeditor.conf.js";
    var el = $('textarea.ckeditor')
    if (el) {
      el.ckeditor();
    }
});

</script>

<!-- end admin head -->
