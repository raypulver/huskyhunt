<?php require_once '../hh-config.php';
/*  error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
error_reporting(-1);  */
$token = HuskyHunt::get_token_object();
$USER = new HuskyHuntUser($token->netid);
$USER->scores();
?> 
