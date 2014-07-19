<?php require_once '../hh-config.php'; ?>
<?php require_once './hh-admin.php'; ?>
<?php $ADMIN->page_validate(); ?>
<?php 

header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=players.txt");

$ADMIN->player_emails();

