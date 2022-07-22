<?php
/*  Telemetry telemetry app Settings files
 *
 *
 */

$_settings = array(
    "domain" => false,

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
        'enabled'   => false,
        'user'      => '',
        'password'  => ''
    ),

// Feed engine settings
    "feed"=>array(
        'engines_hidden'=>array(0,8,10),
        'redisbuffer'   => array(
            'enabled' => false,
            'sleep' => 60
        ),
        'phpfina'       => array('datadir'  => '/var/opt/emoncms/phpfina/'),
        'phptimeseries' => array('datadir'  => '/var/opt/emoncms/phptimeseries/')
    ),

    "interface"=>array(
        'feedviewpath' => "graph/"
    ),

    "public_profile"=>array(

    ),

    "smtp"=>array(
        // Email address to email proccessed input values
        // 'default_emailto' => 'root@localhost',
        // 'host'=>"smtp.gmail.com",
        // 25, 465, 587
        // 'port'=>"465",
        // 'from_email' => 'noreply@emoncms.org',
        // 'from_name' => 'EmonCMS',
        // comment lines below that dont apply
        // ssl, tls
        // 'encryption'=>"ssl",
        // 'username'=>"yourusername@gmail.com",
        // 'password'=>"yourpassword"
    ),

    "log"=>array(
        // Log Level: 1=INFO, 2=WARN, 3=ERROR
        "level" => 2
    )
);
