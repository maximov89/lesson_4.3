<?php
//    define('DB_DRIVER', 'mysql');
//    define('DB_HOST', 'localhost');
//    define('DB_NAME', 'base_1');
//    define('DB_USER', 'root');
//    define('DB_PASS', '');

define('DB_DRIVER', "mysql");
define('DB_HOST', "127.0.0.1");
define('DB_NAME', "amaximov");
define('DB_USER', "amaximov");
define('DB_PASS', "neto1730");

$db_connect = (DB_DRIVER . ":host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8");
$db = new PDO($db_connect, DB_USER, DB_PASS);
if (!$db) {
    die('Не могу подключиться!');
}

//DROP TABLE IF EXISTS `task`;

$table_tasks = $db->exec("CREATE TABLE `task` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `assigned_user_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `is_done` tinyint(4) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

//DROP TABLE IF EXISTS `user`;

$table_users = $db->exec("CREATE TABLE `user` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

