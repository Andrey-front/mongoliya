<?php
// HTTP
define('HTTP_SERVER', 'http://mongoliya.local/');

// HTTPS
define('HTTPS_SERVER', 'http://mongoliya.local/');

// DIR
define('DIR_APPLICATION', '/home/ivan/domains/mongoliya.local/catalog/');
define('DIR_SYSTEM', '/home/ivan/domains/mongoliya.local/system/');
define('DIR_IMAGE', '/home/ivan/domains/mongoliya.local/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'phpmyadmin');
define('DB_PASSWORD', 'secret11');
define('DB_DATABASE', 'mongoliya');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');