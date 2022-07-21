<?php
/*
 * Load routines for setting-up app
 * Carga las rutina para inicializar la aplicacion
 */

define('Telemetry_ENGINE', 1);
chdir("/var/www/emoncms");
require "processing-config.php";
require "libraries/AppLogger.php";
$log = new AppLogger(__FILE__);

// Connect to mysql
// conecta a la base de datos
$mysqli = @new mysqli(
    $settings["sql"]["server"],
    $settings["sql"]["username"],
    $settings["sql"]["password"],
    $settings["sql"]["database"],
    $settings["sql"]["port"]
);

if ($mysqli->connect_error) { 
    $log->error("No se puede conectar a la base de datos mysql:". $mysqli->connect_error);
    die('revisar el log\n');
}

// Connect to redis
// conecta a redis
if ($settings['redis']['enabled']) {
    $redis = new Redis();
    if (!$redis->connect($settings['redis']['host'], $settings['redis']['port'])) {
        $log->error("Cannot connect to redis at ".$settings['redis']['host'].":".$settings['redis']['port']);  die('Check log\n');
    }
    if (!empty($settings['redis']['prefix'])) $redis->setOption(Redis::OPT_PREFIX, $settings['redis']['prefix']);
    if (!empty($settings['redis']['auth'])) {
        if (!$redis->auth($settings['redis']['auth'])) {
            $log->error("Cannot connect to redis at ".$settings['redis']['host'].", autentication failed"); die('Check log\n');
        }
    }
} else {
    $redis = false;
}

$userid = 1; //Numero admin en la base de datos // default number of user admin in the database

require("Ext_Modules/user/user_model.php");
$user = new User($mysqli,$redis,null);

require_once "Ext_Modules/feed/feed_model.php";
$feed = new Feed($mysqli,$redis,$settings['feed']);
