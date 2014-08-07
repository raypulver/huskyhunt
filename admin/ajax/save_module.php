<?php require_once '../../hh-config.php'; ?>
<?php require_once '../hh-admin.php'; ?>
<?php 

$ADMIN->ajax_validate();

$module_id  = post_value('module_id');
$title      = post_value('title');
$body       = post_value('body');
$insight    = post_value('insight');
$points    = post_value('points');
$social_points    = post_value('social_points');
$decay  = post_value('decay');
$postponable    = post_value('postponable');
$bonus    = post_value('bonus');
$knowledge_base    = post_value('knowledge_base');
$vendor = post_value('vendor');
$result     = false;

$module = new HuskyHuntModule($module_id);

if (!is_null($title))
    $module->title = $title;

if (!is_null($body))
    $module->body = $body;

if (!is_null($insight))
    $module->insight = $insight;

if (is_numeric($points)) 
    $module->points = intval($points);

if (is_numeric($social_points)) 
    $module->social_points = intval($social_points);
if (!is_null($decay))
    $module->decay = (strtolower($decay) == 'true');
if (!is_null($postponable)) 
    $module->postponable = (strtolower($postponable) == 'true');

if (!is_null($bonus)) 
    $module->bonus = (strtolower($bonus) == 'true');

if (!is_null($knowledge_base)) 
    $module->knowledge_base = (strtolower($knowledge_base) == 'true');
if (!is_null($vendor))
    $module->vendor = $vendor;

$result = $module->save();

echo json_encode($result);

