<?php

date_default_timezone_set('America/New_York');

define('HH_DEBUG', true);

define('MYSQL_USER', 'huskyhunt');
define('MYSQL_PASS', 'dmx2bnra');
#define('MYSQL_HOST', 'p:');
define('MYSQL_HOST', 'localhost');
define('MYSQL_DATABASE', 'huskyhunt');

define('BASE_PATH', '/var/www');
define('BASE_URL', 'http://ecdesign.co');

include_once BASE_PATH . '/huskyhunt.php';
include_once BASE_PATH . '/functions.php';

# TODO these should be moved into a database for more dynamic deployment, however at this time it is easy enough to manage from the configuration 

define('HH_SOCIAL_TWITTER',     0x01);
define('HH_SOCIAL_FACEBOOK',    0x02);


# TODO include these into core system to allow for custom HuskyHunt interfaces.

define('HH_BRANDNAME', 'HuskyHunt');
define('HH_LOGO', '');
define('HH_ICON', '');
define('HH_CSS_CUSTOM', '');

#$huskyhunt = new HuskyHunt();


