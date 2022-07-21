<?php
/*  Telemetry telemetry app Settings files
 *
 *
 */

$_settings = array(
    "domain" => false,
    "emoncms_dir" => "/opt/emoncms",
    "openenergymonitor_dir" => "/opt/openenergymonitor",
    "display_errors" => true,

// actualiza la base de datos sin autorizacion
    "updatelogin" => false,

// configuracion de la base datos SQL
    "sql"=>array(
        "server"   => "localhost",
        "database" => "emoncms1",
        "username" => "_DB_USER_",
        "password" => "_DB_PASSWORD_",
        "port"     => 3306,
        "dbtest"   => true
    ),

// configuracion de la base datos Redis
// basic setup redis database
    "redis"=>array(
        'enabled' => false,
        'host'    => 'localhost',
        'port'    => 6379,
        'auth'    => '',
        'dbnum'   => '',
        'prefix'  => 'TelemetryDB'
    ),

// configuracion del MQTT
// MQTT basic setup
    "mqtt"=>array(
        'enabled'   => false,
        'host'      => 'localhost',
        'port'      => 1883,
        'user'      => '',
        'password'  => '',
        'basetopic' => 'Telemetry',
        'client_id' => 'Cawpy',
        'userid'    => 1,
        'multiuser' => false
    ),

// maximo de nodos permitidos
// MAX allowed nodes setup
    "input"=>array(
        'max_node_id_limit' => 32
    ),

// configuracion de alimentacion
// feeding system setup
    "feed"=>array(
        'engines_hidden'=>array(
         Engine::MYSQL
        ,Engine::MYSQLMEMORY
        ,Engine::CASSANDRA
        ),

        // Configuracion de baja escritura
        // low write redis configuration
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

// Configuracion de la interface de usuarios
// configuration user interface
    "interface"=>array(

        'appname' => "TelemetryApp",
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
        'enable_multi_user' => false,
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
    // niveles de error: 1=INFORMATION, 2=WARNING, 3=ERROR
    // error messages levels :
    // 1= information, 2=warning, 3=error
    "log"=>array(
        "enabled" => true,
        "location" => "/var/log/Telemetry",

        "level" => 2
    ),

    "device"=>array(
        "enable_UDP_broadcast" => true
    ),

    "cydynni"=>array()
);
