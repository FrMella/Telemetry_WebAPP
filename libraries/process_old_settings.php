<?php
$settings = array(
"domain" => isset($domain)?$domain:false,
"emoncms_dir" => isset($emoncms_dir)?$emoncms_dir:"/home/pi",
"openenergymonitor_dir" => isset($openenergymonitor_dir)?$openenergymonitor_dir:"/home/pi",

"display_errors" => isset($display_errors)?$display_errors:true,


"updatelogin" => isset($updatelogin)?$updatelogin:false,


"sql"=>array(
    "server"   => isset($server)?$server:"localhost",
    "database" => isset($database)?$database:"emoncms",
    "username" => isset($username)?$username:"_DB_USER_",
    "password" => isset($password)?$password:"_DB_PASSWORD_",
    "port"     => isset($port)?$port:3306,
    "dbtest"   => isset($dbtest)?$dbtest:true
),

// Redis
"redis"=>array(
    'enabled' => isset($redis_enabled)?$redis_enabled:false,
    'host'    => isset($redis_server["host"])?$redis_server["host"]:'localhost',
    'port'    => isset($redis_server["port"])?$redis_server["port"]:6379,
    'auth'    => isset($redis_server["auth"])?$redis_server["auth"]:'',
    'dbnum'   => isset($redis_server["dbnum"])?$redis_server["dbnum"]:'',
    'prefix'  => isset($redis_server["prefix"])?$redis_server["prefix"]:'emoncms'
),

// MQTT
"mqtt"=>array(

    'enabled'   => isset($mqtt_enabled)?$mqtt_enabled:false,
    'host'      => isset($mqtt_server["host"])?$mqtt_server["host"]:'localhost',
    'port'      => isset($mqtt_server["port"])?$mqtt_server["port"]:1883,
    'user'      => isset($mqtt_server["user"])?$mqtt_server["user"]:'',
    'password'  => isset($mqtt_server["password"])?$mqtt_server["password"]:'',
    'basetopic' => isset($mqtt_server["basetopic"])?$mqtt_server["basetopic"]:'emon',
    'client_id' => isset($mqtt_server["client_id"])?$mqtt_server["client_id"]:'emoncms',
    'capath'    => isset($mqtt_server["capath"])?$mqtt_server["capath"]:null,
    'certpath'  => isset($mqtt_server["certpath"])?$mqtt_server["certpath"]:null,
    'keypath'   => isset($mqtt_server["keypath"])?$mqtt_server["keypath"]:null,
    'keypw'     => isset($mqtt_server["keypwpath"])?$mqtt_server["keypw"]:null
),

// Input
"input"=>array(
    'max_node_id_limit' => isset($max_node_id_limit)?$max_node_id_limit:32
),


"feed"=>array(

    'engines_hidden'=>isset($feed_settings["engines_hidden"])?$feed_settings["engines_hidden"]:array(
    // Engine::MYSQL         // 0  Mysql traditional
    //,Engine::MYSQLMEMORY   // 8  Mysql with MEMORY tables on RAM. All data is lost on shutdown
    //,Engine::PHPTIMESERIES // 2
    //,Engine::PHPFINA      // 5
    //,Engine::CASSANDRA    // 10 Apache Cassandra
    ),

    // Redis Low-write mode
    'redisbuffer'   => array(
        'enabled' => isset($feed_settings["redisbuffer"]["enabled"])?$feed_settings["redisbuffer"]["enabled"]:false,
        'sleep' => isset($feed_settings["redisbuffer"]["sleep"])?$feed_settings["redisbuffer"]["sleep"]:600
    ),


    'phpfina'       => array('datadir'  => isset($feed_settings["phpfina"]["datadir"])?$feed_settings["phpfina"]["datadir"]:'/var/lib/phpfina/'),
    'phptimeseries' => array('datadir'  => isset($feed_settings["phptimeseries"]["datadir"])?$feed_settings["phptimeseries"]["datadir"]:'/var/lib/phptimeseries/'),
    'cassandra'     => array('keyspace' => isset($feed_settings["cassandra"]["keyspace"])?$feed_settings["cassandra"]["keyspace"]:'emoncms'),
    'virtualfeed'   => array('data_sampling' => false),
    'mysqltimeseries' => array(
        'data_sampling' => false,
        'datadir'       => isset($feed_settings["mysql"]["datadir"])?$feed_settings["mysql"]["datadir"]:'',
        'prefix'        => isset($feed_settings["mysql"]["prefix"])?$feed_settings["mysql"]["prefix"]:'feed_',
        'generic'       => isset($feed_settings["mysql"]["generic"])?$feed_settings["mysql"]["generic"]:true,
        'database'      => isset($feed_settings["mysql"]["database"])?$feed_settings["mysql"]["database"]:null,
        'username'      => isset($feed_settings["mysql"]["username"])?$feed_settings["mysql"]["username"]:null,
        'password'      => isset($feed_settings["mysql"]["password"])?$feed_settings["mysql"]["password"]:null
    ),
    'max_datapoints' => isset($max_datapoints)?$max_datapoints:8928,


    'csv_decimal_places' => isset($csv_decimal_places)?$csv_decimal_places:2,


    'csv_decimal_place_separator' => isset($csv_decimal_place_separator)?$csv_decimal_place_separator:".",

    'csv_field_separator' => isset($csv_field_separator)?$csv_field_separator:",",

    'csv_downloadlimit_mb' => isset($feed_settings["csv_downloadlimit_mb"])?$feed_settings["csv_downloadlimit_mb"]:25
),


"interface"=>array(

    'appname' => isset($appname)?$appname:"emoncms",

    'default_language' => isset($default_language)?$default_language:'en_GB',

    'theme' => isset($theme)?$theme:"basic",
    
    'themecolor' => isset($themecolor)?$themecolor:"blue",


    'favicon' => isset($favicon)?$favicon:"favicon.png",


    'menucollapses' => isset($menucollapses)?$menucollapses:false,
    

    'show_menu_titles' => isset($show_menu_titles)?$show_menu_titles:true,

    'default_controller' => isset($default_controller)?$default_controller:"user",
    'default_action' => isset($default_action)?$default_action:"login",


    'default_controller_auth' => isset($default_controller_auth)?$default_controller_auth:"feed",
    'default_action_auth' => isset($default_action_auth)?$default_action_auth:"list",


    'feedviewpath' => isset($feedviewpath)?$feedviewpath:"vis/auto?feedid=",


    'enable_multi_user' => isset($enable_multi_user)?$enable_multi_user:false,


    'enable_rememberme' => isset($enable_rememberme)?$enable_rememberme:true,


    'enable_password_reset' => isset($enable_password_reset)?$enable_password_reset:false,


    'enable_admin_ui' => isset($allow_emonpi_admin)?$allow_emonpi_admin:false,


    'enable_update_ui' => isset($admin_show_update)?$admin_show_update:true,


    'email_verification' => isset($email_verification)?$email_verification:false
),

"public_profile"=>array(

    'enabled' => isset($public_profile_enabled)?$public_profile_enabled:true,
    'controller' => isset($public_profile_controller)?$public_profile_controller:"dashboard",
    'action' => isset($public_profile_action)?$public_profile_action:"view"
),

"smtp"=>array(
    'default_emailto' => isset($default_emailto)?$default_emailto:'root@localhost',
    
    'host'=>isset($smtp_email_settings["host"])?$smtp_email_settings["host"]:"smtp.gmail.com",

    'port'=>isset($smtp_email_settings["port"])?$smtp_email_settings["port"]:"465",
    'from_email' =>isset($smtp_email_settings["from_email"])?$smtp_email_settings["from_email"]:'noreply@emoncms.org',
    'from_name' =>isset($smtp_email_settings["from_name"])?$smtp_email_settings["from_name"]:'EmonCMS',

    'encryption'=>isset($smtp_email_settings["encryption"])?$smtp_email_settings["encryption"]:"ssl",
    'username'=>isset($smtp_email_settings["username"])?$smtp_email_settings["username"]:"yourusername@gmail.com",
    'password'=>isset($smtp_email_settings["password"])?$smtp_email_settings["password"]:"yourpassword"
),

// Log file configuration
"log"=>array(
    "enabled" => isset($log_enabled)?$log_enabled:true,

    "location" => isset($log_location)?$log_location:"/var/log/emoncms",
    "level" => isset($log_level)?$log_level:2
)
);
