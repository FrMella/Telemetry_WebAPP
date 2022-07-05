<?php
/* Settings app
 *
 *
 */

$_settings = array(
    "domain" => false,
    "CawthronMonitor_dir" => "/opt/CawthronMonitor",
    "cawthron-monitor-app_dir" => "/opt/cawthron-monitor-app",
    "display_errors" => true,

// actualiza la base de datos sin autorizacion
    "updatelogin" => false,

// configuracion de la base datos SQL
    "sql"=>array(
        "server"   => "localhost",
        "database" => "CawthronDB",
        "username" => "_DB_USER_",
        "password" => "_DB_PASSWORD_",
        "port"     => 3306,
        "dbtest"   => true
    ),

// configuracion de la base datos Redis
    "redis"=>array(
        'enabled' => false,
        'host'    => 'localhost',
        'port'    => 6379,
        'auth'    => '',
        'dbnum'   => '',
        'prefix'  => 'CawthronDB'
    ),

// configuracion del MQTT
    "mqtt"=>array(
        'enabled'   => false,
        'host'      => 'localhost',
        'port'      => 1883,
        'user'      => '',
        'password'  => '',
        'basetopic' => 'Cawthron',
        'client_id' => 'Cawpy',
        'userid'    => 1,
        'multiuser' => false
    ),

// maximo de nodos permitidos
    "input"=>array(
        'max_node_id_limit' => 32
    ),

// configuracion de alimentacion
    "feed"=>array(
        'engines_hidden'=>array(
            Engine::MYSQL
        ,Engine::MYSQLMEMORY
        ,Engine::CASSANDRA
        ),

        // Configuracion de baja escritura
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
    "interface"=>array(


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
    // niveles de error: 1=INFORMATION, 2=Warning, 3=ERROR
    "log"=>array(
        "enabled" => true,
        "location" => "/var/log/Cawthron",

        "level" => 2
    ),

    "device"=>array(
        "enable_UDP_broadcast" => true
    ),

    "cydynni"=>array()
);
