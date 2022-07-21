<?php

    
$_settings = array(
    "domain" => false,
    "emoncms_dir" => "/opt/emoncms",
    "openenergymonitor_dir" => "/opt/openenergymonitor",
    "display_errors" => true,
    "updatelogin" => false,

// mysql parametros de configuracion standard
// Mysql database settings
"sql"=>array(
    "server"   => "localhost",
    "database" => "emoncms",
    "username" => "_DB_USER_",
    "password" => "_DB_PASSWORD_",
    "port"     => 3306,
    "dbtest"   => true
),

// configuracion redis
// Redis
"redis"=>array(
    'enabled' => false,
    'host'    => 'localhost',
    'port'    => 6379,
    'auth'    => '',
    'dbnum'   => '',
    'prefix'  => 'emoncms'
),

// todo: implementar y conectar el servidor mqtt
// MQTT
"mqtt"=>array(

    'enabled'   => false,
    'host'      => 'localhost',
    'port'      => 1883,
    'user'      => '',
    'password'  => '',
    'basetopic' => 'emon',
    'client_id' => 'emoncms',
    'userid'    => 1,
    'multiuser' => false
),

// limite de nods posibles
// node input possible
"input"=>array(
    'max_node_id_limit' => 32
),

// Configuracion de alimentador de la base de datos
// Feed settings
"feed"=>array(

    'engines_hidden'=>array(
     Engine::MYSQL
    ,Engine::MYSQLMEMORY
    //,Engine::PHPTIMESERIES
    //,Engine::PHPFINA
    ,Engine::CASSANDRA
    ),

    // parametros de escritura lenta para redis
    // Redis Low-write mode

    'redisbuffer'   => array(
        'enabled' => false,
        'sleep' => 60
    ),
    

    'phpfina'       => array('datadir'  => '/var/lib/phpfina/'),
    'phptimeseries' => array('datadir'  => '/var/lib/phptimeseries/'),
    'cassandra'     => array('keyspace' => 'emoncms'),
    'virtualfeed'   => array('data_sampling' => false),
    'mysqltimeseries'   => array('data_sampling' => false),
    'max_datapoints'        => 8928,
    
    'csv_decimal_places' => 2,
    'csv_decimal_place_separator' => ".",
    'csv_field_separator' => ",",
    'csv_downloadlimit_mb' => 25
),

"interface"=>array(

    'appname' => "TelemetryAPP",
    'default_language' => 'en_GB',
    'theme' => "basic",
    'themecolor' => "blue",
    'favicon' => "favicon.png",
    'menucollapses' => false,
    'show_menu_titles' => true,
    'default_controller' => "user",
    'default_action' => "login",
    'default_controller_auth' => "feed",
    'default_action_auth' => "list",
    'feedviewpath' => "vis/auto?feedid=",
    'enable_multi_user' => true,
    'enable_rememberme' => true,
    'enable_password_reset' => false,
    'enable_admin_ui' => false,
    'enable_update_ui' => true,
    'email_verification' => false
),

"public_profile"=>array(
    'enabled' => true,
    'controller' => "dashboard",
    'action' => "view"
),

"smtp"=>array(
    'default_emailto' => '',
    'from_email' => '',
    'from_name' => '',
    'sendmail' => false,
    'host'=>"",
    'port'=>"",
    'encryption'=>"",
    'username'=>"",
    'password'=>""
),

"log"=>array(
    "enabled" => true,
    "location" => "/var/log/emoncms",
    "level" => 2
),

"device"=>array(
    "enable_UDP_broadcast" => true
),

"cydynni"=>array()
);
